<?php

namespace App\Services;

use App\Models\AiConversation;
use App\Models\AiMessage;
use App\Models\MaintenanceRequest;
use App\Models\Property;
use App\Models\RentInvoice;
use App\Models\RentPayment;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class KiradaAssistantService
{
    private bool $enabled;

    public function __construct()
    {
        $this->enabled = filled(config('services.openai.key'));
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    /**
     * Build a role-scoped system prompt with real data context.
     * AI can ONLY read — never write/create/update/delete.
     */
    public function buildSystemPrompt(User $user): string
    {
        $base = <<<'PROMPT'
You are Kirada Assistant, a helpful read-only AI for the Kirada rent management platform.

CRITICAL RULES:
1. You can ONLY summarize and report data shown to you in the context below.
2. You MUST NEVER create, update, delete, confirm, reject, assign, or modify any data.
3. You MUST NEVER suggest the user perform destructive actions.
4. If asked to perform a write action, politely explain you are read-only for now.
5. Answer concisely — short paragraphs or bullet lists.
6. If the data is not in your context, say you don't have that information.
7. Do not invent numbers, names, or dates. Only use what's provided.
8. Format currency as the property's currency. If unknown, say "DJF" as default.
PROMPT;

        $context = $this->buildContext($user);

        return $base . "\n\n--- USER CONTEXT ---\n" . $context;
    }

    /**
     * Build role-specific data context that the AI can reference.
     */
    public function buildContext(User $user): string
    {
        if ($user->isAdmin()) {
            return $this->adminContext();
        }

        if ($user->isLandlord()) {
            return $this->landlordContext($user);
        }

        if ($user->isTenant()) {
            return $this->tenantContext($user);
        }

        if ($user->isMaintenance()) {
            return $this->maintenanceContext($user);
        }

        return "No context available for this user role.";
    }

    private function adminContext(): string
    {
        $landlords = User::role('landlord')->count();
        $tenants = Tenant::count();
        $properties = Property::count();
        $activeLeases = \App\Models\Lease::where('status', 'active')->count();
        $unpaidInvoices = RentInvoice::where('status', 'unpaid')->count();
        $openMaintenance = MaintenanceRequest::whereIn('status', ['open', 'in_progress'])->count();

        return <<<CTX
Role: Administrator
Total landlords: {$landlords}
Total tenants: {$tenants}
Total properties: {$properties}
Active leases: {$activeLeases}
Unpaid invoices: {$unpaidInvoices}
Open/in-progress maintenance requests: {$openMaintenance}
CTX;
    }

    private function landlordContext(User $user): string
    {
        $propertyIds = Property::forLandlord($user->id)->pluck('id');
        $myProperties = $propertyIds->count();
        $myUnits = \App\Models\Unit::whereIn('property_id', $propertyIds)->count();
        $occupied = \App\Models\Unit::whereIn('property_id', $propertyIds)->where('status', 'occupied')->count();
        $vacant = \App\Models\Unit::whereIn('property_id', $propertyIds)->where('status', 'vacant')->count();
        $activeLeases = \App\Models\Lease::whereIn('property_id', $propertyIds)->where('status', 'active')->count();
        $unpaidInvoices = RentInvoice::forLandlord($user->id)->where('status', 'unpaid')->count();
        $overdueInvoices = RentInvoice::forLandlord($user->id)->overdue()->count();
        $collectedThisMonth = RentPayment::where('landlord_id', $user->id)
            ->where('status', 'confirmed')
            ->whereMonth('payment_date', now()->month)
            ->whereYear('payment_date', now()->year)
            ->sum('amount');
        $openMaintenance = MaintenanceRequest::forLandlord($user->id)
            ->whereIn('status', ['open', 'in_progress'])
            ->count();

        // Late tenants
        $lateTenants = RentInvoice::forLandlord($user->id)
            ->overdue()
            ->with('tenant:id,first_name,last_name')
            ->limit(10)
            ->get()
            ->map(fn ($inv) => "{$inv->tenant?->first_name} {$inv->tenant?->last_name} — {$inv->invoice_number}")
            ->implode("\n");

        return <<<CTX
Role: Landlord (ID: {$user->id})
My properties: {$myProperties}
My units: {$myUnits} ({$occupied} occupied, {$vacant} vacant)
Active leases: {$activeLeases}
Unpaid invoices: {$unpaidInvoices}
Overdue invoices: {$overdueInvoices}
Collected this month: {$collectedThisMonth} DJF
Open/in-progress maintenance: {$openMaintenance}

Late tenants:
{$lateTenants}
CTX;
    }

    private function tenantContext(User $user): string
    {
        $tenant = Tenant::where('user_id', $user->id)->first();

        if (!$tenant) {
            return "Role: Tenant\nNo tenant profile linked to this account.";
        }

        $lease = \App\Models\Lease::where('tenant_id', $tenant->id)->where('status', 'active')->first();
        $leaseInfo = 'No active lease';
        $rentAmount = 0;
        $dueDay = 1;

        if ($lease) {
            $rentAmount = $lease->monthly_rent;
            $dueDay = $lease->payment_due_day;
            $leaseInfo = "Property: {$lease->property?->name}, Unit: {$lease->unit?->unit_number}, Rent: {$rentAmount} DJF/month, Due day: {$dueDay}, End date: {$lease->end_date?->format('M j, Y')}";
        }

        $currentInvoice = RentInvoice::where('tenant_id', $tenant->id)
            ->whereIn('status', ['unpaid', 'partially_paid'])
            ->latest()
            ->first();

        $invoiceInfo = $currentInvoice
            ? "Invoice {$currentInvoice->invoice_number}, amount: {$currentInvoice->amount} DJF, status: {$currentInvoice->status}, due: {$currentInvoice->due_date?->format('M j, Y')}"
            : 'No outstanding invoices';

        $paymentCount = RentPayment::where('tenant_id', $tenant->id)
            ->where('status', 'confirmed')
            ->count();

        $openMaintenance = MaintenanceRequest::forTenant($tenant->id)
            ->whereIn('status', ['open', 'in_progress'])
            ->count();

        return <<<CTX
Role: Tenant (ID: {$tenant->id})
Active lease: {$leaseInfo}
Current invoice: {$invoiceInfo}
Confirmed payments made: {$paymentCount}
Open/in-progress maintenance requests: {$openMaintenance}
CTX;
    }

    private function maintenanceContext(User $user): string
    {
        $assigned = MaintenanceRequest::assignedTo($user->id);
        $open = (clone $assigned)->whereIn('status', ['open'])->count();
        $inProgress = (clone $assigned)->whereIn('status', ['in_progress'])->count();
        $urgent = (clone $assigned)->whereIn('status', ['open', 'in_progress'])->where('priority', 'urgent')->count();

        $recentList = (clone $assigned)->with(['property:id,name', 'unit:id,unit_number'])
            ->latest()->limit(10)->get()
            ->map(fn ($r) => "#{$r->id} {$r->title} — {$r->property?->name} / {$r->unit?->unit_number} [{$r->status}, {$r->priority}]")
            ->implode("\n");

        return <<<CTX
Role: Maintenance staff (ID: {$user->id})
Assigned open: {$open}
Assigned in-progress: {$inProgress}
Urgent (open + in-progress): {$urgent}

Recent assigned requests:
{$recentList}
CTX;
    }

    /**
     * Send a message to OpenAI and get a response.
     * Stores both user message and assistant reply.
     */
    public function chat(AiConversation $conversation, string $userMessage): string
    {
        if (!$this->enabled) {
            return $this->disabledMessage();
        }

        // Store user message
        $conversation->aiMessages()->create([
            'role' => 'user',
            'content' => $userMessage,
        ]);

        // Build message array for API
        $systemPrompt = $this->buildSystemPrompt($conversation->user);
        $history = $conversation->aiMessages()
            ->orderBy('created_at')
            ->get()
            ->map(fn ($m) => ['role' => $m->role, 'content' => $m->content])
            ->toArray();

        $messages = array_merge(
            [['role' => 'system', 'content' => $systemPrompt]],
            $history
        );

        try {
            $client = \OpenAI::factory()
                ->withApiKey(config('services.openai.key'))
                ->make();

            $response = $client->chat()->create([
                'model' => $conversation->model,
                'messages' => $messages,
                'max_tokens' => 800,
                'temperature' => 0.3,
            ]);

            $reply = $response->choices[0]->message->content ?? 'No response generated.';
            $inputTokens = $response->usage->promptTokens ?? null;
            $outputTokens = $response->usage->completionTokens ?? null;

            // Store assistant reply
            $conversation->aiMessages()->create([
                'role' => 'assistant',
                'content' => $reply,
                'input_tokens' => $inputTokens,
                'output_tokens' => $outputTokens,
            ]);

            $conversation->update(['last_message_at' => now()]);

            return $reply;

        } catch (\Throwable $e) {
            Log::error('Kirada AI Assistant error: ' . $e->getMessage());

            $errorMsg = 'Sorry, I could not process your request right now. Please try again later.';
            $conversation->aiMessages()->create([
                'role' => 'assistant',
                'content' => $errorMsg,
            ]);

            return $errorMsg;
        }
    }

    /**
     * Start a new conversation for a user.
     */
    public function startConversation(User $user, string $title = 'New Conversation'): AiConversation
    {
        return AiConversation::create([
            'user_id' => $user->id,
            'title' => $title,
            'model' => 'gpt-4o-mini',
            'system_context' => ['role' => $user->roles->first()?->name],
        ]);
    }

    private function disabledMessage(): string
    {
        return 'Kirada AI Assistant is not configured. Please add an OpenAI API key to the services configuration to enable AI features.';
    }
}
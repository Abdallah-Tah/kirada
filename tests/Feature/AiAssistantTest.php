<?php

namespace Tests\Feature;

use App\Models\AiConversation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AiAssistantTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $landlord;
    private User $tenant;
    private User $maintenance;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('db:seed', ['--class' => 'RolePermissionSeeder']);
        $this->artisan('db:seed', ['--class' => 'CountryCurrencySeeder']);
        $this->artisan('db:seed', ['--class' => 'PlanSeeder']);
        $this->artisan('db:seed', ['--class' => 'AdminUserSeeder']);

        $this->admin = User::where('email', 'admin@kirada.dj')->first();
        $this->landlord = User::where('email', 'landlord@kirada.dj')->first();
        $this->tenant = User::where('email', 'tenant@kirada.dj')->first();
        $this->maintenance = User::where('email', 'maintenance@kirada.dj')->first();
    }

    public function test_guests_cannot_access_ai_assistant(): void
    {
        $response = $this->get(route('ai-assistant.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_admin_can_access_ai_assistant(): void
    {
        $response = $this->actingAs($this->admin)->get(route('ai-assistant.index'));
        $response->assertStatus(200);
    }

    public function test_landlord_can_access_ai_assistant(): void
    {
        $response = $this->actingAs($this->landlord)->get(route('ai-assistant.index'));
        $response->assertStatus(200);
    }

    public function test_tenant_can_access_ai_assistant(): void
    {
        $response = $this->actingAs($this->tenant)->get(route('ai-assistant.index'));
        $response->assertStatus(200);
    }

    public function test_maintenance_can_access_ai_assistant(): void
    {
        $response = $this->actingAs($this->maintenance)->get(route('ai-assistant.index'));
        $response->assertStatus(200);
    }

    public function test_ai_assistant_page_loads_for_all_roles(): void
    {
        // The page should load regardless of whether the API key is set
        $response = $this->actingAs($this->admin)->get(route('ai-assistant.index'));
        $response->assertStatus(200);
        // Should either show the chat UI or the "not configured" notice
        $response->assertSee('Kirada');
    }

    public function test_conversation_is_scoped_to_user(): void
    {
        // Admin creates a conversation
        $conv = AiConversation::create([
            'user_id' => $this->admin->id,
            'title' => 'Admin chat',
            'model' => 'gpt-4o-mini',
        ]);

        // Landlord should not see admin's conversation
        $response = $this->actingAs($this->landlord)->get(route('ai-assistant.index'));
        $response->assertDontSee('Admin chat');
    }
}
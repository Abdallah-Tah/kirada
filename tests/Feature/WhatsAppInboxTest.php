<?php

namespace Tests\Feature;

use App\Livewire\WhatsApp\Index;
use App\Models\Tenant;
use App\Models\User;
use App\Models\WhatsAppMessage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Inbound that matches no tenant belongs to no landlord, so the inbox has to
 * both keep it away from landlords and stop it disappearing entirely.
 */
class WhatsAppInboxTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        $this->artisan('db:seed', ['--class' => 'RolePermissionSeeder']);
    }

    public function test_a_landlord_never_sees_unmatched_messages(): void
    {
        $landlord = $this->landlord();
        $this->message(null, null);
        $mine = $this->message($landlord->id, null);

        Livewire::actingAs($landlord)
            ->test(Index::class)
            ->assertSee($mine->body)
            ->assertDontSee('Unmatched')
            ->assertSet('unmatchedOnly', false);
    }

    public function test_an_admin_sees_unmatched_messages_and_their_count(): void
    {
        $admin = $this->admin();
        $this->message(null, null);
        $this->message(null, null);

        Livewire::actingAs($admin)
            ->test(Index::class)
            ->assertSee('Unmatched')
            ->assertSee('Unmatched only');

        $this->assertSame(2, Livewire::actingAs($admin)->test(Index::class)->instance()->unmatchedCount());
    }

    public function test_the_unmatched_filter_narrows_the_list_for_an_admin(): void
    {
        $admin = $this->admin();
        $landlord = $this->landlord();
        $matched = $this->message($landlord->id, null, 'matched body');
        $orphan = $this->message(null, null, 'orphan body');

        Livewire::actingAs($admin)
            ->test(Index::class)
            ->assertSee($matched->body)
            ->assertSee($orphan->body)
            ->set('unmatchedOnly', true)
            ->assertDontSee($matched->body)
            ->assertSee($orphan->body);
    }

    public function test_a_landlord_cannot_mark_an_unmatched_message_as_read(): void
    {
        $landlord = $this->landlord();
        $orphan = $this->message(null, null);

        Livewire::actingAs($landlord)
            ->test(Index::class)
            ->call('markAsRead', $orphan->id)
            ->assertForbidden();

        $this->assertNull($orphan->refresh()->read_at);
    }

    private function admin(): User
    {
        $user = User::factory()->create();
        $user->assignRole('admin');

        return $user;
    }

    private function landlord(): User
    {
        $user = User::factory()->create();
        $user->assignRole('landlord');

        return $user;
    }

    private function message(?int $landlordId, ?Tenant $tenant, string $body = 'hello'): WhatsAppMessage
    {
        return WhatsAppMessage::create([
            'landlord_id' => $landlordId,
            'tenant_id' => $tenant?->id,
            'provider_message_id' => 'wamid.'.uniqid(),
            'from_number' => '12074097887',
            'message_type' => 'text',
            'body' => $body,
            'payload' => [],
            'received_at' => now(),
        ]);
    }
}

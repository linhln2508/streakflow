<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserApprovalTest extends TestCase
{
    use RefreshDatabase;

    public function test_pending_user_cannot_login_via_web_api(): void
    {
        User::factory()->pending()->create([
            'email' => 'pending@example.com',
            'password' => Hash::make('password'),
        ]);

        $this->postJson('/web_api/auth/login', [
            'email' => 'pending@example.com',
            'password' => 'password',
        ])->assertStatus(422)
            ->assertJsonPath('errors.email.0', __('auth.not_approved'));

        $this->assertGuest();
    }

    public function test_pending_user_cannot_login_via_api(): void
    {
        User::factory()->pending()->create([
            'email' => 'pending@example.com',
            'password' => Hash::make('password'),
        ]);

        $this->postJson('/api/login', [
            'email' => 'pending@example.com',
            'password' => 'password',
            'device_name' => 'phpunit',
        ])->assertStatus(403)
            ->assertJsonPath('message', __('auth.not_approved'));
    }

    public function test_admin_can_approve_pending_user(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $pending = User::factory()->pending()->create();

        $this->actingAs($admin)
            ->patchJson(route('web_api.admin.users.approve', $pending))
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertTrue($pending->fresh()->isApproved());
    }

    public function test_admin_can_reject_pending_user(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $pending = User::factory()->pending()->create();

        $this->actingAs($admin)
            ->deleteJson(route('web_api.admin.users.reject', $pending))
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseMissing('users', ['id' => $pending->id]);
    }

    public function test_approved_user_can_login_after_admin_approval(): void
    {
        $user = User::factory()->pending()->create([
            'email' => 'approved@example.com',
            'password' => Hash::make('password'),
        ]);

        $user->update(['is_approved' => true, 'approved_at' => now()]);

        $this->postJson('/web_api/auth/login', [
            'email' => 'approved@example.com',
            'password' => 'password',
        ])->assertOk()
            ->assertJsonPath('success', true);

        $this->assertAuthenticated();
    }
}

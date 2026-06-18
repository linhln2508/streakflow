<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminPasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_non_admin_cannot_reset_user_password(): void
    {
        $user = User::factory()->create();
        $target = User::factory()->create();

        $this->actingAs($user)
            ->putJson(route('web_api.admin.users.reset_password', $target), [
                'password' => 'new-password-1',
                'password_confirmation' => 'new-password-1',
            ])
            ->assertForbidden();
    }

    public function test_admin_can_reset_user_password(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create([
            'password' => Hash::make('old-password'),
        ]);

        $this->actingAs($admin)
            ->putJson(route('web_api.admin.users.reset_password', $user), [
                'password' => 'new-password-1',
                'password_confirmation' => 'new-password-1',
            ])
            ->assertOk()
            ->assertJsonPath('message', __('admin.password_reset'));

        $this->assertTrue(Hash::check('new-password-1', $user->fresh()->password));
    }

    public function test_admin_cannot_reset_admin_password(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $otherAdmin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->putJson(route('web_api.admin.users.reset_password', $otherAdmin), [
                'password' => 'new-password-1',
                'password_confirmation' => 'new-password-1',
            ])
            ->assertStatus(422)
            ->assertJsonPath('message', __('auth.user_password_reset_admin_forbidden'));
    }

    public function test_password_confirmation_is_required(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create();

        $this->actingAs($admin)
            ->putJson(route('web_api.admin.users.reset_password', $user), [
                'password' => 'new-password-1',
                'password_confirmation' => 'different-password',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }
}

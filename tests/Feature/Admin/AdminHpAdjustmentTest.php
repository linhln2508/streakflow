<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminHpAdjustmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_non_admin_cannot_adjust_hp(): void
    {
        $user = User::factory()->create(['hp' => -59]);
        $other = User::factory()->create();

        $this->actingAs($user)
            ->patchJson(route('web_api.admin.users.adjust_hp', $other), ['amount' => 59])
            ->assertForbidden();
    }

    public function test_admin_can_top_up_negative_hp(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['hp' => -59]);

        $this->actingAs($admin)
            ->patchJson(route('web_api.admin.users.adjust_hp', $user), [
                'amount' => 59,
                'note' => 'Bù HP thủ công',
            ])
            ->assertOk()
            ->assertJsonPath('data.hp_before', -59)
            ->assertJsonPath('data.hp_after', 0)
            ->assertJsonPath('data.amount', 59)
            ->assertJsonPath('message', __('admin.hp_adjusted'));

        $this->assertSame(0, $user->fresh()->hp);
    }

    public function test_admin_hp_adjustment_caps_at_100(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['hp' => 90]);

        $this->actingAs($admin)
            ->patchJson(route('web_api.admin.users.adjust_hp', $user), ['amount' => 50])
            ->assertOk()
            ->assertJsonPath('data.hp_after', 100);

        $this->assertSame(100, $user->fresh()->hp);
    }

    public function test_zero_amount_is_rejected(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['hp' => 10]);

        $this->actingAs($admin)
            ->patchJson(route('web_api.admin.users.adjust_hp', $user), ['amount' => 0])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['amount']);
    }
}

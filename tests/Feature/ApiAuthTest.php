<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ApiAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_login_and_create_category_via_api(): void
    {
        $user = User::factory()->create([
            'email' => 'api@linhtinh.test',
            'password' => Hash::make('password'),
        ]);

        $login = $this->postJson('/api/login', [
            'email' => 'api@linhtinh.test',
            'password' => 'password',
            'device_name' => 'phpunit',
        ]);

        $login->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure(['data' => ['token', 'user']]);

        $token = $login->json('data.token');

        $this->withToken($token)
            ->postJson('/api/categories', [
                'name' => 'API Category',
                'color' => '#112233',
                'icon' => 'Heart',
            ])
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.name', 'API Category');

        $this->withToken($token)
            ->getJson('/api/me')
            ->assertOk()
            ->assertJsonPath('data.email', 'api@linhtinh.test');

        $this->withToken($token)
            ->postJson('/api/logout')
            ->assertOk()
            ->assertJsonPath('success', true);
    }

    public function test_login_fails_with_invalid_credentials(): void
    {
        User::factory()->create([
            'email' => 'api@linhtinh.test',
            'password' => Hash::make('password'),
        ]);

        $this->postJson('/api/login', [
            'email' => 'api@linhtinh.test',
            'password' => 'wrong-password',
            'device_name' => 'phpunit',
        ])->assertStatus(401)
            ->assertJsonPath('success', false);
    }
}

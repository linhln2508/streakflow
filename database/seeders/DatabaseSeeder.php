<?php

namespace Database\Seeders;

use App\Models\Badge;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $badges = [
            ['key' => 'streak_7', 'name' => 'Streak 7 Ngày', 'description' => 'Duy trì streak 7 ngày liên tiếp', 'icon' => '🔥'],
            ['key' => 'streak_30', 'name' => 'Streak 30 Ngày', 'description' => 'Duy trì streak 30 ngày liên tiếp', 'icon' => '💪'],
            ['key' => 'streak_100', 'name' => 'Streak 100 Ngày', 'description' => 'Duy trì streak 100 ngày liên tiếp', 'icon' => '👑'],
            ['key' => 'perfect_day', 'name' => 'Ngày Hoàn Hảo', 'description' => 'Hoàn thành 100% task trong một ngày', 'icon' => '⭐'],
            ['key' => 'perfect_week', 'name' => 'Tuần Hoàn Hảo', 'description' => 'Hoàn thành 100% task 7 ngày liên tiếp', 'icon' => '🏆'],
            ['key' => 'level_5', 'name' => 'Level 5', 'description' => 'Đạt level 5', 'icon' => '🎯'],
            ['key' => 'level_10', 'name' => 'Level 10', 'description' => 'Đạt level 10', 'icon' => '🌟'],
            ['key' => 'hp_max', 'name' => 'HP Tối Đa', 'description' => 'Đạt 100 HP', 'icon' => '❤️'],
        ];

        foreach ($badges as $badge) {
            Badge::updateOrCreate(['key' => $badge['key']], $badge);
        }

        User::updateOrCreate(
            ['email' => 'admin@streakflow.test'],
            [
                'name' => 'Admin',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'email_verified_at' => now(),
            ]
        );

        User::updateOrCreate(
            ['email' => 'user@streakflow.test'],
            [
                'name' => 'Demo User',
                'password' => Hash::make('password'),
                'role' => 'user',
                'email_verified_at' => now(),
            ]
        );
    }
}

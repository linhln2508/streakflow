<?php

namespace Database\Seeders;

use App\Actions\GenerateDailyTaskInstancesAction;
use App\Models\Category;
use App\Models\TaskTemplate;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::where('email', 'user@linhtinh.test')->first();

        if (!$user) {
            return;
        }

        $health = Category::updateOrCreate(
            ['user_id' => $user->id, 'name' => 'Sức khỏe'],
            ['color' => '#EF4444', 'icon' => 'Heart'],
        );

        $work = Category::updateOrCreate(
            ['user_id' => $user->id, 'name' => 'Công việc'],
            ['color' => '#3B82F6', 'icon' => 'Briefcase'],
        );

        $personal = Category::updateOrCreate(
            ['user_id' => $user->id, 'name' => 'Cá nhân'],
            ['color' => '#8B5CF6', 'icon' => 'User'],
        );

        $templates = [
            [
                'title' => 'Uống 2L nước',
                'category_id' => $health->id,
                'priority' => 'high',
                'recurrence_type' => 'daily',
                'recurrence_config' => null,
                'sort_order' => 1,
            ],
            [
                'title' => 'Tập thể dục 30 phút',
                'category_id' => $health->id,
                'priority' => 'high',
                'recurrence_type' => 'weekdays',
                'recurrence_config' => null,
                'sort_order' => 2,
            ],
            [
                'title' => 'Review OKR tuần',
                'category_id' => $work->id,
                'priority' => 'medium',
                'recurrence_type' => 'weekly',
                'recurrence_config' => ['days' => [1, 5]],
                'sort_order' => 3,
            ],
            [
                'title' => 'Trả hóa đơn',
                'category_id' => $personal->id,
                'priority' => 'medium',
                'recurrence_type' => 'monthly',
                'recurrence_config' => ['days' => [1, 15]],
                'sort_order' => 4,
            ],
            [
                'title' => 'Deep clean nhà',
                'category_id' => $personal->id,
                'priority' => 'low',
                'recurrence_type' => 'custom',
                'recurrence_config' => ['interval' => 3, 'unit' => 'day'],
                'sort_order' => 5,
            ],
            [
                'title' => 'Họp team kickoff',
                'category_id' => $work->id,
                'priority' => 'high',
                'recurrence_type' => 'one_time',
                'recurrence_config' => ['date' => Carbon::today()->addDays(7)->toDateString()],
                'sort_order' => 6,
            ],
        ];

        foreach ($templates as $data) {
            TaskTemplate::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'title' => $data['title'],
                ],
                array_merge($data, [
                    'user_id' => $user->id,
                    'start_date' => Carbon::today()->subMonth(),
                    'end_date' => null,
                    'is_active' => true,
                ]),
            );
        }

        app(GenerateDailyTaskInstancesAction::class)->execute(Carbon::today());
    }
}

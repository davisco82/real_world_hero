<?php

namespace Database\Seeders;

use App\Models\Achievement;
use App\Models\Child;
use App\Models\RoutineTemplate;
use App\Models\SkillDomain;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $child = Child::query()->firstOrCreate([
            'name' => 'Alex',
        ]);

        $domains = [
            'Přežití',
            'Práce s časem',
            'Sebeřízení',
            'Tým a rodina',
            'Emoční inteligence',
            'Sociální dovednosti',
            'Peníze a hodnota',
            'Zdraví a energie',
        ];

        foreach ($domains as $name) {
            SkillDomain::query()->firstOrCreate(['name' => $name]);
        }

        $today = now()->toDateString();

        $routineTemplates = [
            [
                'domain' => 'Zdraví a energie',
                'title' => '20 minut pohybu',
                'description' => 'Ranní pohyb pro energický start dne',
                'period' => 'morning',
                'base_xp' => 5,
                'bonus_xp' => 60,
                'goal_type' => 'streak',
                'goal_target' => 5,
                'window_days' => null,
            ],
            [
                'domain' => 'Práce s časem',
                'title' => 'Dokonči domácí úkol před večeří',
                'description' => 'Pravidelný režim školních povinností',
                'period' => 'afternoon',
                'base_xp' => 5,
                'bonus_xp' => 80,
                'goal_type' => 'volume',
                'goal_target' => 10,
                'window_days' => 14,
            ],
            [
                'domain' => 'Přežití',
                'title' => 'Připrav si školní tašku',
                'description' => 'Večerní příprava bez ranního stresu',
                'period' => 'evening',
                'base_xp' => 5,
                'bonus_xp' => 50,
                'goal_type' => 'streak',
                'goal_target' => 4,
                'window_days' => null,
            ],
        ];

        foreach ($routineTemplates as $template) {
            $domain = SkillDomain::query()->where('name', $template['domain'])->firstOrFail();

            RoutineTemplate::query()->updateOrCreate(
                ['title' => $template['title']],
                [
                    'skill_domain_id' => $domain->id,
                    'description' => $template['description'],
                    'period' => $template['period'],
                    'base_xp' => $template['base_xp'],
                    'bonus_xp' => $template['bonus_xp'],
                    'goal_type' => $template['goal_type'],
                    'goal_target' => $template['goal_target'],
                    'window_days' => $template['window_days'],
                    'active_from' => $today,
                    'active_until' => null,
                    'is_active' => true,
                ]
            );
        }

        Achievement::query()->firstOrCreate(
            ['code' => 'first_mission_approved'],
            [
                'title' => 'První potvrzená mise',
                'description' => 'První mise potvrzená rodičem',
            ]
        );
    }
}

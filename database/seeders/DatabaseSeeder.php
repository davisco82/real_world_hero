<?php

namespace Database\Seeders;

use App\Models\Achievement;
use App\Models\Child;
use App\Models\Mission;
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

        $missionTemplates = [
            ['Přežití', 'Připrav si školní tašku', 20],
            ['Práce s časem', 'Dokonči domácí úkol před večeří', 30],
            ['Zdraví a energie', '20 minut pohybu', 25],
        ];

        foreach ($missionTemplates as [$domainName, $title, $xp]) {
            $domain = SkillDomain::query()->where('name', $domainName)->firstOrFail();

            Mission::query()->firstOrCreate([
                'skill_domain_id' => $domain->id,
                'title' => $title,
                'mission_date' => $today,
            ], [
                'xp_reward' => $xp,
            ]);
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

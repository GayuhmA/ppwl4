<?php

namespace Database\Seeders;

use App\Models\Skill;
use Illuminate\Database\Seeder;

class SkillSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            ['name' => 'Laravel', 'level' => 85],
            ['name' => 'PHP', 'level' => 80],
            ['name' => 'JavaScript', 'level' => 75],
        ] as $skill) {
            Skill::updateOrCreate(['name' => $skill['name']], $skill);
        }
    }
}

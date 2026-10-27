<?php

namespace Database\Seeders;

use App\Models\Project;
use Faker\Factory;
use Illuminate\Database\Seeder;

class RandomProjectTableSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Factory::create('id_ID');
        // Seed tetap membuat judul acak yang sama saat seeder dijalankan ulang.
        $faker->seed(8);

        for ($i = 1; $i <= 25; $i++) {
            Project::firstOrCreate(
                ['title' => 'Project '.$faker->unique()->sentence(3)],
                ['description' => $faker->paragraph()]
            );
        }
    }
}

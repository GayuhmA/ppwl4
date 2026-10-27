<?php

namespace Database\Seeders;

use App\Models\Project;
use Illuminate\Database\Seeder;

class ProjectTableSeeder extends Seeder
{
    public function run(): void
    {
        for ($i = 1; $i <= 10; $i++) {
            Project::firstOrCreate(
                ['title' => 'Project Judul Ke-'.$i],
                ['description' => 'Deskripsi untuk project judul ke-'.$i]
            );
        }
    }
}

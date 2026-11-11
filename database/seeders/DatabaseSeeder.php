<?php

namespace Database\Seeders;

use App\Models\Project;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        Project::create(['name' => 'Kakotora Line 1', 'slug' => 'kakotora-line-1', 'description' => 'Database aktivitas sendiri']);
        Project::create(['name' => 'Kakotora Line 4', 'slug' => 'kakotora-line-4', 'description' => 'Database aktivitas sendiri']);
    }
}
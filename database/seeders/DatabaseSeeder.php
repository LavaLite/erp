<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Call the MultiOrganizationSeeder to seed all data
        $this->call([
            MultiOrganizationSeeder::class,
            TeamSeeder::class,
            RoleModuleSeeder::class,
        ]);
    }
}

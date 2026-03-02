<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class BangladeshDemoDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call([
            BangladeshTuitionTaxonomySeeder::class,
            BangladeshDemoUsersSeeder::class,
            BangladeshDemoJobsSeeder::class,
        ]);
    }
}

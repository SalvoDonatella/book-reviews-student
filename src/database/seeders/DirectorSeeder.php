<?php

namespace Database\Seeders;

use App\Models\Director;
use Illuminate\Database\Seeder;

class DirectorSeeder extends Seeder
{
    /**
     * Seed the directors table.
     */
    public function run(): void
    {
        Director::factory(10)->create();
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Src\Domain\Magistrate\Models\Magistrate;

class MagistrateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Magistrate::factory(10)->create();
    }
}

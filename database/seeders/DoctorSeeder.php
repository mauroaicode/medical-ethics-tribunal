<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Src\Domain\Doctor\Models\Doctor;

class DoctorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Doctor::factory(15)->create();
    }
}

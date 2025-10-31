<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Src\Domain\Complainant\Models\Complainant;
use Src\Domain\City\Models\City;

class ComplainantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $cities = City::all();

        Complainant::factory(20)->create()->each(function ($complainant) use ($cities) {
            if ($cities->isNotEmpty()) {
                $complainant->update([
                    'city_id' => $cities->random()->id,
                ]);
            }
        });
    }
}

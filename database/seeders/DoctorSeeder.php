<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Src\Domain\Doctor\Models\Doctor;
use Src\Domain\MedicalSpecialty\Models\MedicalSpecialty;

class DoctorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $specialties = MedicalSpecialty::all();

        if ($specialties->isEmpty()) {
            $this->command->warn('No hay especialidades médicas disponibles. Ejecuta primero MedicalSpecialtySeeder.');

            return;
        }

        Doctor::factory(15)->create()->each(function ($doctor) use ($specialties): void {
            if (! $doctor->specialty_id) {
                $doctor->update([
                    'specialty_id' => $specialties->random()->id,
                ]);
            }
        });
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Src\Domain\Complainant\Models\Complainant;
use Src\Domain\Doctor\Models\Doctor;
use Src\Domain\Magistrate\Models\Magistrate;
use Src\Domain\Process\Models\Process;

class ProcessSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $complainants = Complainant::all();
        $doctors = Doctor::all();
        $magistrates = Magistrate::all();

        if ($complainants->isEmpty() || $doctors->isEmpty() || $magistrates->count() < 2) {
            $this->command->warn('Se necesitan al menos 2 magistrados para crear procesos.');

            return;
        }

        // Obtener el siguiente ID disponible para generar números de proceso únicos
        $lastProcessId = Process::max('id') ?? 0;

        for ($i = 1; $i <= 30; $i++) {
            $processId = $lastProcessId + $i;
            $processNumber = 'PRO-'.Str::padLeft((string) $processId, 4, '0');

            Process::factory()->create([
                'complainant_id' => $complainants->random()->id,
                'doctor_id' => $doctors->random()->id,
                'magistrate_instructor_id' => $magistrates->random()->id,
                'magistrate_ponente_id' => $magistrates->random()->id,
                'process_number' => $processNumber,
            ]);
        }
    }
}

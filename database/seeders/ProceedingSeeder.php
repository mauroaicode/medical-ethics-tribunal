<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Src\Domain\Proceeding\Models\Proceeding;
use Src\Domain\Process\Models\Process;

class ProceedingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $processes = Process::all();

        if ($processes->isEmpty()) {
            $this->command->warn('Se necesitan procesos para crear actuaciones.');

            return;
        }

        foreach ($processes as $process) {
            // Cada proceso puede tener entre 1 y 5 actuaciones
            $count = fake()->numberBetween(1, 5);
            Proceeding::factory($count)->create([
                'process_id' => $process->id,
                'proceeding_date' => fake()->dateTimeBetween($process->start_date, 'now'),
            ]);
        }
    }
}

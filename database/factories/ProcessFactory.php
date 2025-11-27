<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Src\Domain\Complainant\Models\Complainant;
use Src\Domain\Doctor\Models\Doctor;
use Src\Domain\Magistrate\Models\Magistrate;
use Src\Domain\Process\Enums\ProcessStatus;
use Src\Domain\Process\Models\Process;

/**
 * @extends Factory<Process>
 */
class ProcessFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<Process>
     */
    protected $model = Process::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        static $counter = 0;
        $counter++;
        $uniqueId = (int) (microtime(true) * 100000) + $counter;

        $name = fake()->sentence(4);
        $processNumber = sprintf('PRO-%06d', $uniqueId % 1000000);
        $slug = Str::slug($name).'-'.Str::lower($processNumber);

        return [
            'complainant_id' => Complainant::factory(),
            'doctor_id' => Doctor::factory(),
            'magistrate_instructor_id' => Magistrate::factory(),
            'magistrate_ponente_id' => Magistrate::factory(),
            'name' => $name,
            'slug' => $slug,
            'process_number' => $processNumber,
            'start_date' => fake()->dateTimeBetween('-2 years', 'now'),
            'status' => fake()->randomElement(ProcessStatus::cases())->value,
            'description' => fake()->paragraph(),
        ];
    }
}

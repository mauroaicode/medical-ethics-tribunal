<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Src\Domain\Complainant\Models\Complainant;
use Src\Domain\Doctor\Models\Doctor;
use Src\Domain\Magistrate\Models\Magistrate;
use Src\Domain\Process\Enums\ProcessStatus;
use Src\Domain\Process\Models\Process;
use Src\Domain\Template\Models\Template;

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

        return [
            'complainant_id' => Complainant::factory(),
            'doctor_id' => Doctor::factory(),
            'magistrate_instructor_id' => Magistrate::factory(),
            'magistrate_ponente_id' => Magistrate::factory(),
            'template_id' => Template::exists() && fake()->boolean(70)
                ? Template::inRandomOrder()->first()?->id
                : null,
            'name' => fake()->sentence(4),
            'process_number' => sprintf('PRO-%06d', $uniqueId % 1000000),
            'start_date' => fake()->dateTimeBetween('-2 years', 'now'),
            'status' => fake()->randomElement(ProcessStatus::cases())->value,
            'description' => fake()->paragraph(),
        ];
    }
}

<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Src\Domain\Proceeding\Models\Proceeding;
use Src\Domain\Process\Models\Process;

/**
 * @extends Factory<Proceeding>
 */
class ProceedingFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<Proceeding>
     */
    protected $model = Proceeding::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'process_id' => Process::factory(),
            'name' => fake()->words(3, true),
            'description' => fake()->sentence(),
            'proceeding_date' => fake()->dateTimeBetween('-1 year', 'now'),
        ];
    }
}

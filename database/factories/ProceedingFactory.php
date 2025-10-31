<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Src\Domain\Process\Models\Process;
use Src\Domain\Proceeding\Models\Proceeding;

/**
 * @extends Factory<Proceeding>
 */
class ProceedingFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<Model>
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
            'description' => fake()->sentence(),
            'proceeding_date' => fake()->dateTimeBetween('-1 year', 'now'),
        ];
    }
}

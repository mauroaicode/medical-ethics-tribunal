<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Src\Domain\Complainant\Models\Complainant;
use Src\Domain\User\Models\User;

/**
 * @extends Factory<Complainant>
 */
class ComplainantFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<Model>
     */
    protected $model = Complainant::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            // city_id se asigna en el seeder desde los datos SQL
            'city_id' => 1, // Valor temporal, será reemplazado en el seeder
            'municipality' => fake()->optional()->city(),
            'company' => fake()->optional()->company(),
            'is_anonymous' => fake()->boolean(20), // 20% probabilidad de ser anónimo
        ];
    }
}

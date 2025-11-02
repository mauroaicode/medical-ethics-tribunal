<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Src\Domain\Magistrate\Models\Magistrate;
use Src\Domain\User\Models\User;

/**
 * @extends Factory<Magistrate>
 */
class MagistrateFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<Magistrate>
     */
    protected $model = Magistrate::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
        ];
    }
}

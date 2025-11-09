<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Src\Domain\SessionBlock\Models\SessionBlock;
use Src\Domain\User\Models\User;

/**
 * @extends Factory<SessionBlock>
 */
class SessionBlockFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<SessionBlock>
     */
    protected $model = SessionBlock::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'session_id' => fake()->optional()->uuid(),
            'ip_address' => fake()->ipv4(),
            'user_agent' => fake()->userAgent(),
            'action' => fake()->randomElement(['process.update', 'process.delete']),
            'blocked_until' => now()->addHour(),
        ];
    }
}


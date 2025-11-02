<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Src\Domain\Session\Models\Session;
use Src\Domain\User\Models\User;

/**
 * @extends Factory<Session>
 */
class SessionFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<Session>
     */
    protected $model = Session::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id' => Str::random(40),
            'user_id' => User::factory(),
            'ip_address' => fake()->ipv4(),
            'user_agent' => fake()->userAgent(),
            'location' => fake()->optional()->randomElement([
                'Bogotá, Cundinamarca, Colombia',
                'Medellín, Antioquia, Colombia',
                'Cali, Valle del Cauca, Colombia',
                'Barranquilla, Atlántico, Colombia',
                'Cartagena, Bolívar, Colombia',
                'Pereira, Risaralda, Colombia',
                'Santa Marta, Magdalena, Colombia',
                'Manizales, Caldas, Colombia',
                'Popayán, Cauca, Colombia',
                null,
            ]),
            'payload' => serialize(['login_at' => now()->toDateTimeString()]),
            'last_activity' => now()->timestamp,
        ];
    }
}

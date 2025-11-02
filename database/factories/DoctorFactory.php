<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Src\Domain\Doctor\Models\Doctor;
use Src\Domain\User\Models\User;

/**
 * @extends Factory<Doctor>
 */
class DoctorFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<Doctor>
     */
    protected $model = Doctor::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'specialty' => fake()->randomElement([
                'Cardiología',
                'Dermatología',
                'Ginecología',
                'Neurología',
                'Pediatría',
                'Traumatología',
                'Medicina Interna',
                'Cirugía General',
            ]),
            'faculty' => fake()->randomElement([
                'Universidad Nacional',
                'Universidad de Antioquia',
                'Universidad Javeriana',
                'Universidad del Rosario',
                'Universidad de los Andes',
            ]),
            'medical_registration_number' => fake()->unique()->numerify('M#######'),
            'medical_registration_place' => fake()->city(),
            'medical_registration_date' => fake()->dateTimeBetween('-20 years', '-1 year'),
            'main_practice_company' => fake()->optional()->company(),
            'other_practice_company' => fake()->optional()->company(),
        ];
    }
}

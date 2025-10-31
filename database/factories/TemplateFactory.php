<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Src\Domain\Template\Models\Template;

/**
 * @extends Factory<Template>
 */
class TemplateFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<Model>
     */
    protected $model = Template::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->randomElement([
                'Plantilla de Citación',
                'Plantilla de Resolución',
                'Plantilla de Notificación',
                'Plantilla de Audiencia',
                'Plantilla de Fallo',
            ]),
            'description' => fake()->optional()->sentence(),
        ];
    }
}

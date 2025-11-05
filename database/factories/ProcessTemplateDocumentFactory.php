<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Src\Domain\Process\Models\Process;
use Src\Domain\ProcessTemplateDocument\Models\ProcessTemplateDocument;
use Src\Domain\Template\Models\Template;

/**
 * @extends Factory<ProcessTemplateDocument>
 */
class ProcessTemplateDocumentFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<ProcessTemplateDocument>
     */
    protected $model = ProcessTemplateDocument::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'process_id' => Process::factory(),
            'template_id' => Template::factory(),
            'google_drive_file_id' => fake()->unique()->uuid(),
            'file_name' => fake()->unique()->word().'.docx',
            'local_path' => fake()->optional()->filePath(),
            'google_docs_name' => fake()->word(),
        ];
    }
}


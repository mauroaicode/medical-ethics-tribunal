<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Src\Domain\Template\Models\Template;

class TemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Template::factory(5)->create();
    }
}

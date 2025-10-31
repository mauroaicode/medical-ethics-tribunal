<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Src\Domain\Zone\Models\Zone;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        Zone::factory(10)->create();

        $path = database_path('seeders/sql/departamentos.sql');
        DB::unprepared(file_get_contents($path));
        $path = database_path('seeders/sql/ciudades.sql');
        DB::unprepared(file_get_contents($path));

        $this->call([
            RoleSeeder::class,
            UserSeeder::class,
            ComplainantSeeder::class,
            DoctorSeeder::class,
            MagistrateSeeder::class,
            TemplateSeeder::class,
            ProcessSeeder::class,
            ProceedingSeeder::class,
            AuditLogSeeder::class,
        ]);
    }
}

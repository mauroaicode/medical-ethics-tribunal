<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Src\Domain\AuditLog\Models\AuditLog;
use Src\Domain\Complainant\Models\Complainant;
use Src\Domain\Doctor\Models\Doctor;
use Src\Domain\Magistrate\Models\Magistrate;
use Src\Domain\Process\Models\Process;
use Src\Domain\Template\Models\Template;
use Src\Domain\User\Models\User;

class AuditLogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();
        $models = [
            ['model' => Process::class, 'name' => 'Process'],
            ['model' => Complainant::class, 'name' => 'Complainant'],
            ['model' => Doctor::class, 'name' => 'Doctor'],
            ['model' => Magistrate::class, 'name' => 'Magistrate'],
            ['model' => Template::class, 'name' => 'Template'],
        ];

        $actions = ['created', 'updated', 'deleted', 'viewed'];

        foreach ($models as $modelData) {
            $instances = $modelData['model']::all();

            foreach ($instances as $instance) {
                $action = fake()->randomElement($actions);
                $user = $users->random();

                AuditLog::create([
                    'user_id' => $user->id,
                    'action' => $action,
                    'model_name' => $modelData['name'],
                    'model_id' => $instance->id,
                    'old_values' => $action === 'created' ? null : ['field' => 'old_value'],
                    'new_values' => $action === 'deleted' ? null : ['field' => 'new_value'],
                    'ip_address' => fake()->ipv4(),
                    'user_agent' => fake()->userAgent(),
                    'created_at' => fake()->dateTimeBetween('-6 months', 'now'),
                ]);
            }
        }
    }
}

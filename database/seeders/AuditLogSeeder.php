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
            Process::class,
            Complainant::class,
            Doctor::class,
            Magistrate::class,
            Template::class,
        ];

        $actions = ['create', 'update', 'delete', 'view'];

        foreach ($models as $modelClass) {
            $instances = $modelClass::all();

            foreach ($instances as $instance) {
                $action = fake()->randomElement($actions);
                $user = $users->random();

                AuditLog::create([
                    'user_id' => $user->id,
                    'action' => $action,
                    'auditable_type' => $modelClass,
                    'auditable_id' => $instance->id,
                    'old_values' => $action === 'create' ? null : ['field' => 'old_value'],
                    'new_values' => $action === 'delete' ? null : ['field' => 'new_value'],
                    'ip_address' => fake()->ipv4(),
                    'user_agent' => fake()->userAgent(),
                    'created_at' => fake()->dateTimeBetween('-6 months', 'now'),
                ]);
            }
        }
    }
}

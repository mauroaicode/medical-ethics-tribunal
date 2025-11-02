<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Src\Domain\User\Enums\DocumentType;
use Src\Domain\User\Enums\UserStatus;
use Src\Domain\User\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Usuario super admin
        $superAdmin = User::factory()->create([
            'name' => 'Super',
            'last_name' => 'Administrador',
            'document_type' => DocumentType::CEDULA_CIUDADANIA->value,
            'document_number' => '1000000001',
            'email' => 'superadmin@tribunal.com',
            'password' => Hash::make('password'),
            'status' => UserStatus::ACTIVE->value,
            'email_verified_at' => now(),
        ]);
        $superAdmin->assignRole('super_admin');

        // Usuario admin
        $admin = User::factory()->create([
            'name' => 'Admin',
            'last_name' => 'Sistema',
            'document_type' => DocumentType::CEDULA_CIUDADANIA->value,
            'document_number' => '1000000002',
            'email' => 'admin@tribunal.com',
            'password' => Hash::make('password'),
            'status' => UserStatus::ACTIVE->value,
            'email_verified_at' => now(),
        ]);
        $admin->assignRole('admin');

        // Usuario secretaria
        $secretary = User::factory()->create([
            'name' => 'Secretaria',
            'last_name' => 'General',
            'document_type' => DocumentType::CEDULA_CIUDADANIA->value,
            'document_number' => '1000000003',
            'email' => 'secretaria@tribunal.com',
            'password' => Hash::make('password'),
            'status' => UserStatus::ACTIVE->value,
            'email_verified_at' => now(),
        ]);
        $secretary->assignRole('secretary');

        // Crear usuarios adicionales con roles aleatorios
        $roles = Role::all();
        User::factory(10)->create()->each(function ($user) use ($roles) {
            $user->assignRole($roles->random());
        });
    }
}

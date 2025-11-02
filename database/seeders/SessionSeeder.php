<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Src\Domain\Session\Models\Session;
use Src\Domain\User\Models\User;

class SessionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();

        if ($users->isEmpty()) {
            $this->command->warn('No hay usuarios disponibles. Ejecuta primero UserSeeder.');

            return;
        }

        // Create sessions for each user (some recent, some old)
        foreach ($users as $user) {
            // Recent sessions (active)
            Session::factory(2)->create([
                'user_id' => $user->id,
                'last_activity' => now()->subMinutes(random_int(5, 120))->timestamp,
            ]);

            // Older sessions (inactive)
            Session::factory(3)->create([
                'user_id' => $user->id,
                'last_activity' => now()->subDays(random_int(1, 30))->timestamp,
            ]);
        }
    }
}

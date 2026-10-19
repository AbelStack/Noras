<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        if (User::where('role', 'admin')->exists()) {
            $this->command->info('Admin account already exists. Skipping.');
            return;
        }

        User::create([
            'name' => 'Admin User',
            'email' => 'admin@noras.test',
            'password' => 'admin123',
            'student_id' => 'ADMIN001',
            'role' => 'admin',
            'account_status' => 'approved',
            'approved_at' => now(),
        ]);

        $this->command->info('Development admin created: admin@noras.test');
        $this->command->warn('Change these credentials before deploying to production!');
    }
}

<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        if (app()->environment('production')) {
            $this->command->error('Cannot seed development admin in production environment.');
            $this->command->warn('Create admin accounts manually in production with secure credentials.');
            return;
        }

        if (User::where('email', 'admin@noras.test')->exists()) {
            $this->command->info('Development admin already exists. Skipping.');
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

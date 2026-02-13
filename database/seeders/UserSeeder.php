<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Admin User (Allan - Lead Developer)
        $admin = User::create([
            'name' => 'Allan (Admin)',
            'email' => 'allan@commercialloan.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        $admin->assignRole('admin');
        $this->command->info('✓ Created Admin: allan@commercialloan.com');

        // Create Admin User (Allan - Lead Developer)
        $admin = User::create([
            'name' => 'System',
            'email' => 'system@internal.local',
            'password' => Hash::make(Str::random(40)),
            'email_verified_at' => now(),
        ]);
        $admin->assignRole('system');
        $this->command->info('✓ Created System: system@internal.local');

        // Create Assessor Users
        $assessor1 = User::create([
            'name' => 'Aurelio (Assessor - API)',
            'email' => 'aurelio@commercialloan.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        $assessor1->assignRole('assessor');
        $this->command->info('✓ Created Assessor: aurelio@commercialloan.com');

        $assessor2 = User::create([
            'name' => 'Jeffrey (Assessor)',
            'email' => 'jeffrey@commercialloan.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        $assessor2->assignRole('assessor');
        $this->command->info('✓ Created Assessor: jeffrey@commercialloan.com');

        // Create additional assessor
        $assessor3 = User::create([
            'name' => 'Cindy (Assessor - Living Expenses)',
            'email' => 'cindy@commercialloan.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        $assessor3->assignRole('assessor');
        $this->command->info('✓ Created Assessor: cindy@commercialloan.com');

        // Create Test Clients
        $client1 = User::create([
            'name' => 'John Smith',
            'email' => 'john.smith@example.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        $client1->assignRole('client');
        $this->command->info('✓ Created Test Client: john.smith@example.com');

        $client2 = User::create([
            'name' => 'Jane Doe',
            'email' => 'jane.doe@example.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        $client2->assignRole('client');
        $this->command->info('✓ Created Test Client: jane.doe@example.com');

        $this->command->info('');
        $this->command->info('=================================');
        $this->command->info('Default password for all users: password');
        $this->command->info('=================================');
    }
}

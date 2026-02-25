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
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        $admin->assignRole('admin');
        $this->command->info('✓ Created Admin: admin@example.com');

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
            'name' => 'Assessor 1',
            'email' => 'assessor1@example.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        $assessor1->assignRole('assessor');
        $this->command->info('✓ Created Assessor: assessor1@example.com');

        $assessor2 = User::create([
            'name' => 'Assessor 2',
            'email' => 'assessor2@example.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        $assessor2->assignRole('assessor');
        $this->command->info('✓ Created Assessor: assessor2@example.com');

        // Create additional assessor
        $assessor3 = User::create([
            'name' => 'Assessor 3',
            'email' => 'assessor3@example.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        $assessor3->assignRole('assessor');
        $this->command->info('✓ Created Assessor: assessor3@example.com');

        // Create Test Clients
        $client1 = User::create([
            'name' => 'Client 1',
            'email' => 'client1@example.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        $client1->assignRole('client');
        $this->command->info('✓ Created Test Client: client1@example.com');

        $client2 = User::create([
            'name' => 'Client 2',
            'email' => 'client2@example.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        $client2->assignRole('client');
        $this->command->info('✓ Created Test Client: client2@example.com');

        $this->command->info('');
        $this->command->info('=================================');
        $this->command->info('Default password for all users: password');
        $this->command->info('=================================');
    }
}

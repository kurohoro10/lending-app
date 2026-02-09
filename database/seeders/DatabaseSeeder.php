<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            UserSeeder::class,
        ]);

        $this->command->info('');
        $this->command->info('====================================');
        $this->command->info('Database seeding completed!');
        $this->command->info('====================================');
        $this->command->info('');
        $this->command->info('You can now login with:');
        $this->command->info('Admin: allan@commercialloan.com / password');
        $this->command->info('Assessor: aurelio@commercialloan.com / password');
        $this->command->info('Client: john.smith@example.com / password');
        $this->command->info('');
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = [
            // Application permissions
            'view applications',
            'create applications',
            'edit applications',
            'delete applications',
            'submit applications',

            // Review permissions
            'review applications',
            'approve applications',
            'decline applications',
            'assign applications',

            // Document permissions
            'upload documents',
            'review documents',
            'request documents',

            // Communication permissions
            'send emails',
            'send sms',
            'view communications',

            // Assessment permissions
            'verify living expenses',
            'perform credit checks',
            'create tasks',
            'complete tasks',

            // Comment permissions
            'add comments',
            'view internal comments',

            // Admin permissions
            'manage users',
            'view audit logs',
            'export reports',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web'
            ]);
        }

        // Create roles and assign permissions

        // Admin Role - Full access
        $adminRole = Role::firstOrCreate([
            'name' => 'admin',
            'guard_name' => 'web',
        ]);

        $adminRole->givePermissionTo(Permission::all());

        // System Role - For automated system actions
        Role::firstOrCreate([
            'name' => 'system',
            'guard_name' => 'web',
        ]);

        // Assessor Role - Review and assessment
        $assessorRole = Role::firstOrCreate([
            'name' => 'assessor',
            'guard_name' => 'web',
        ]);

        $assessorRole->givePermissionTo([
            'view applications',
            'review applications',
            'approve applications',
            'decline applications',
            'review documents',
            'request documents',
            'send emails',
            'send sms',
            'view communications',
            'verify living expenses',
            'perform credit checks',
            'create tasks',
            'complete tasks',
            'add comments',
            'view internal comments',
            'view audit logs',
            'export reports',
        ]);

        // Client Role - Limited to own applications
        $clientRole = Role::firstOrCreate([
            'name' => 'client',
            'guard_name' => 'web',
        ]);
        $clientRole->givePermissionTo([
            'view applications',
            'create applications',
            'edit applications',
            'submit applications',
            'upload documents',
            'add comments',
        ]);

        $this->command->info('Roles and permissions created successfully!');
        $this->command->info('Created roles: admin, assessor, client');
        $this->command->info('Total permissions: ' . count($permissions));
    }
}

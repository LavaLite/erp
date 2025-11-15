<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class MultiOrganizationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create global roles (available across all organizations)
        $this->command->info('Creating global roles...');
        
        $superAdminRole = Role::firstOrCreate(
            ['organization_id' => 'global', 'slug' => 'superadmin'],
            [
                'name' => 'Super Administrator',
                'description' => 'Global super administrator with full system access',
            ]
        );

        $adminRole = Role::firstOrCreate(
            ['organization_id' => 'global', 'slug' => 'admin'],
            [
                'name' => 'Administrator',
                'description' => 'Global administrator role',
            ]
        );

        $userRole = Role::firstOrCreate(
            ['organization_id' => 'global', 'slug' => 'user'],
            [
                'name' => 'User',
                'description' => 'Global user role',
            ]
        );

        $clientRole = Role::firstOrCreate(
            ['organization_id' => 'global', 'slug' => 'client'],
            [
                'name' => 'Client',
                'description' => 'Global client role',
            ]
        );

        $userAdminRole = Role::firstOrCreate(
            ['organization_id' => 'global', 'slug' => 'user-admin'],
            [
                'name' => 'User Admin',
                'description' => 'Global user administrator role - manages users only',
            ]
        );

        // Create sample organizations/companies
        $organization1 = Organization::firstOrCreate(
            ['slug' => 'acme'],
            [
                'name' => 'Acme Corporation',
                'domain' => 'acme.example.com',
                'description' => 'Leading provider of innovative solutions',
                'is_active' => true,
            ]
        );

        $organization2 = Organization::firstOrCreate(
            ['slug' => 'techstart'],
            [
                'name' => 'TechStart Inc',
                'domain' => 'techstart.example.com',
                'description' => 'Technology startup company',
                'is_active' => true,
            ]
        );

        // Create global permissions
        $this->command->info('Creating global permissions...');
        
        $globalPermissionsData = [
            // Super admin permissions
            ['name' => 'Manage All Organizations', 'slug' => 'manage-all-organizations'],
            ['name' => 'View All Data', 'slug' => 'view-all-data'],
            ['name' => 'Assign Super Admin Role', 'slug' => 'assign-superadmin-role'],
            
            // Global admin permissions (user, role, permission management)
            ['name' => 'Manage Global Roles', 'slug' => 'manage-global-roles'],
            ['name' => 'View Users', 'slug' => 'view-users'],
            ['name' => 'Create Users', 'slug' => 'create-users'],
            ['name' => 'Edit Users', 'slug' => 'edit-users'],
            ['name' => 'Delete Users', 'slug' => 'delete-users'],
            ['name' => 'View Roles', 'slug' => 'view-roles'],
            ['name' => 'Create Roles', 'slug' => 'create-roles'],
            ['name' => 'Edit Roles', 'slug' => 'edit-roles'],
            ['name' => 'Delete Roles', 'slug' => 'delete-roles'],
            ['name' => 'Assign Roles', 'slug' => 'assign-roles'],
            ['name' => 'View Permissions', 'slug' => 'view-permissions'],
            ['name' => 'Create Permissions', 'slug' => 'create-permissions'],
            ['name' => 'Edit Permissions', 'slug' => 'edit-permissions'],
            ['name' => 'Delete Permissions', 'slug' => 'delete-permissions'],
            ['name' => 'Assign Permissions', 'slug' => 'assign-permissions'],
        ];

        $globalPermissions = [];
        foreach ($globalPermissionsData as $perm) {
            $globalPermissions[] = Permission::firstOrCreate(
                ['organization_id' => 'global', 'slug' => $perm['slug']],
                [
                    'name' => $perm['name'],
                    'description' => $perm['name'] . ' - Global permission',
                ]
            );
        }

        // Assign all global permissions to super admin role
        $superAdminRole->permissions()->sync(collect($globalPermissions)->pluck('id'));
        
        // Assign user/role/permission management permissions to admin role
        $adminPermissions = Permission::where('organization_id', 'global')
            ->whereIn('slug', [
                'manage-global-roles',
                'view-users', 'create-users', 'edit-users', 'delete-users',
                'view-roles', 'create-roles', 'edit-roles', 'delete-roles',
                'assign-roles',
                'view-permissions', 'create-permissions', 'edit-permissions', 'delete-permissions',
                'assign-permissions',
            ])->get();
        $adminRole->permissions()->sync($adminPermissions);

        // Assign user management permissions to user-admin role
        $userAdminPermissions = Permission::where('organization_id', 'global')
            ->whereIn('slug', [
                'view-users', 'create-users', 'edit-users', 'delete-users',
            ])->get();
        $userAdminRole->permissions()->sync($userAdminPermissions);

        // Create sample users
        // Global Admin user (first user - manages global roles and permissions)
        $globalAdmin = User::create([
            'first_name' => 'Global',
            'last_name' => 'Admin',
            'email' => 'globaladmin@lavalite.org',
            'password' => Hash::make('password'),
            'phone' => '+1-555-0101',
            'date_of_birth' => '1985-06-20',
            'gender' => 'female',
            'city' => 'Seattle',
            'state' => 'WA',
            'country' => 'USA',
            'timezone' => 'America/Los_Angeles',
            'language' => 'en',
            'bio' => 'Global Administrator with system-wide management capabilities',
        ]);

        // Super Admin
        $superAdmin = User::create([
            'first_name' => 'Super',
            'last_name' => 'Admin',
            'email' => 'superadmin@lavalite.org',
            'password' => Hash::make('password'),
            'phone' => '+1-555-0103',
            'date_of_birth' => '1990-05-15',
            'gender' => 'male',
            'city' => 'New York',
            'state' => 'NY',
            'country' => 'USA',
            'timezone' => 'America/New_York',
            'language' => 'en',
            'bio' => 'Super Administrator with full system access',
        ]);

        // User Admin (manages users only)
        $userAdmin = User::create([
            'first_name' => 'User',
            'last_name' => 'Admin',
            'email' => 'useradmin@lavalite.org',
            'password' => Hash::make('password'),
            'phone' => '+1-555-0102',
            'date_of_birth' => '1987-09-15',
            'gender' => 'male',
            'city' => 'Boston',
            'state' => 'MA',
            'country' => 'USA',
            'timezone' => 'America/New_York',
            'language' => 'en',
            'bio' => 'User Administrator responsible for managing users',
        ]);

        // Regular User
        $user = User::create([
            'first_name' => 'Regular',
            'last_name' => 'User',
            'email' => 'user@lavalite.org',
            'password' => Hash::make('password'),
            'phone' => '+1-555-0104',
            'date_of_birth' => '1992-08-22',
            'gender' => 'female',
            'city' => 'San Francisco',
            'state' => 'CA',
            'country' => 'USA',
            'timezone' => 'America/Los_Angeles',
            'language' => 'en',
            'bio' => 'Regular user with basic permissions',
        ]);

        // Client User
        $client = User::create([
            'first_name' => 'Client',
            'last_name' => 'User',
            'email' => 'client@lavalite.org',
            'password' => Hash::make('password'),
            'phone' => '+1-555-0105',
            'date_of_birth' => '1988-03-10',
            'gender' => 'male',
            'city' => 'Austin',
            'state' => 'TX',
            'country' => 'USA',
            'timezone' => 'America/Chicago',
            'language' => 'en',
            'bio' => 'Client user with limited access',
        ]);

        // Add users to organizations with roles
        // Global Admin: Has global admin role in both organizations
        $this->command->info('Assigning roles to Global Admin...');
        $globalAdmin->joinOrganization($organization1);
        $globalAdmin->assignRoleInOrganization('admin', $organization1);
        
        $globalAdmin->joinOrganization($organization2);
        $globalAdmin->assignRoleInOrganization('admin', $organization2);

        // User Admin: Has user-admin role in both organizations
        $this->command->info('Assigning roles to User Admin...');
        $userAdmin->joinOrganization($organization1);
        $userAdmin->assignRoleInOrganization('user-admin', $organization1);
        
        $userAdmin->joinOrganization($organization2);
        $userAdmin->assignRoleInOrganization('user-admin', $organization2);

        // Super Admin: Super Admin role in both organizations
        $this->command->info('Assigning roles to Super Admin...');
        $superAdmin->joinOrganization($organization1);
        $superAdmin->assignRoleInOrganization('superadmin', $organization1);
        
        $superAdmin->joinOrganization($organization2);
        $superAdmin->assignRoleInOrganization('superadmin', $organization2);

        // Regular User: User role in both organizations
        $this->command->info('Assigning roles to Regular User...');
        $user->joinOrganization($organization1);
        $user->assignRoleInOrganization('user', $organization1);
        
        $user->joinOrganization($organization2);
        $user->assignRoleInOrganization('user', $organization2);

        // Client: Client role in both organizations
        $this->command->info('Assigning roles to Client...');
        $client->joinOrganization($organization1);
        $client->assignRoleInOrganization('client', $organization1);
        
        $client->joinOrganization($organization2);
        $client->assignRoleInOrganization('client', $organization2);

        $this->command->info('Multi-organization data seeded successfully!');
        $this->command->info('');
        $this->command->info('Global Roles created:');
        $this->command->info('- superadmin (Super Administrator)');
        $this->command->info('- admin (Administrator)');
        $this->command->info('- user-admin (User Admin)');
        $this->command->info('- user (User)');
        $this->command->info('- client (Client)');
        $this->command->info('');
        $this->command->info('Organizations created:');
        $this->command->info('1. Acme Corporation (slug: acme)');
        $this->command->info('2. TechStart Inc (slug: techstart)');
        $this->command->info('');
        $this->command->info('Users created:');
        $this->command->info('- globaladmin@lavalite.org (GLOBAL ADMIN - admin role in both organizations, can manage global roles/permissions)');
        $this->command->info('- useradmin@lavalite.org (USER ADMIN - user-admin role in both organizations, can manage users)');
        $this->command->info('- superadmin@lavalite.org (SUPER ADMIN - superadmin role in both organizations)');
        $this->command->info('- user@lavalite.org (USER - user role in both organizations)');
        $this->command->info('- client@lavalite.org (CLIENT - client role in both organizations)');
        $this->command->info('');
        $this->command->info('Password for all users: password');
    }
}


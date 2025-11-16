<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Global Permissions
        $permissions = [
            ['name' => 'View Users', 'slug' => 'view-users', 'description' => 'Can view users list', 'organization_id' => 'global'],
            ['name' => 'Create Users', 'slug' => 'create-users', 'description' => 'Can create new users', 'organization_id' => 'global'],
            ['name' => 'Edit Users', 'slug' => 'edit-users', 'description' => 'Can edit existing users', 'organization_id' => 'global'],
            ['name' => 'Delete Users', 'slug' => 'delete-users', 'description' => 'Can delete users', 'organization_id' => 'global'],

            ['name' => 'View Roles', 'slug' => 'view-roles', 'description' => 'Can view roles', 'organization_id' => 'global'],
            ['name' => 'Create Roles', 'slug' => 'create-roles', 'description' => 'Can create roles', 'organization_id' => 'global'],
            ['name' => 'Edit Roles', 'slug' => 'edit-roles', 'description' => 'Can edit roles', 'organization_id' => 'global'],
            ['name' => 'Delete Roles', 'slug' => 'delete-roles', 'description' => 'Can delete roles', 'organization_id' => 'global'],

            ['name' => 'Assign Roles', 'slug' => 'assign-roles', 'description' => 'Can assign roles to users', 'organization_id' => 'global'],
            ['name' => 'Assign Permissions', 'slug' => 'assign-permissions', 'description' => 'Can assign permissions', 'organization_id' => 'global'],
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(
                ['slug' => $permission['slug'], 'organization_id' => 'global'],
                $permission
            );
        }

        // Create Global Roles
        $adminRole = Role::firstOrCreate(
            ['slug' => 'admin', 'organization_id' => 'global'],
            [
                'name' => 'Administrator',
                'description' => 'Has full access to the system',
                'organization_id' => 'global',
            ]
        );

        $userRole = Role::firstOrCreate(
            ['slug' => 'user', 'organization_id' => 'global'],
            [
                'name' => 'User',
                'description' => 'Regular user with basic permissions',
                'organization_id' => 'global',
            ]
        );

        // Assign all permissions to admin
        $adminRole->permissions()->sync(Permission::where('organization_id', 'global')->get());

        $this->command->info('Global Roles and Permissions seeded successfully!');
    }
}

<?php

namespace Database\Seeders;

use App\Models\Module;
use App\Models\Organization;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleModuleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Demonstrates hybrid module access control:
     * - Different roles get access to different modules
     * - Viewers: Only basic modules (Dashboard, Reports)
     * - Members: Basic + operational modules
     * - Managers: Members + management modules
     * - Admins/Owners: All modules
     */
    public function run(): void
    {
        // Get all organizations
        $organizations = Organization::all();

        if ($organizations->isEmpty()) {
            $this->command->warn('No organizations found. Please run DatabaseSeeder first.');

            return;
        }

        foreach ($organizations as $organization) {
            $this->command->info("Setting up role-module access for: {$organization->name}");

            // Use global roles for each organization
            $globalRoles = Role::whereIn('name', ['Super Administrator', 'Administrator', 'User', 'User Admin', 'Client'])->get();

            // Get enabled modules for this organization
            $enabledModuleIds = DB::table('organization_module')
                ->where('organization_id', $organization->id)
                ->where('is_enabled', true)
                ->pluck('module_id')
                ->toArray();

            if (empty($enabledModuleIds)) {
                $this->command->warn("  No enabled modules for {$organization->name}");

                continue;
            }

            // Define module categories and their access levels
            $basicModuleSlugs = ['dash', 'rpt', 'cal'];
            $operationalModuleSlugs = ['crm', 'inv', 'proj', 'doc', 'chat'];
            $managementModuleSlugs = ['hr', 'acc', 'sale', 'purch', 'payroll'];
            $advancedModuleSlugs = ['bi', 'auto', 'api', 'audit'];

            // Get module IDs by slugs
            $basicModules = Module::whereIn('slug', $basicModuleSlugs)
                ->whereIn('id', $enabledModuleIds)
                ->pluck('id')
                ->toArray();

            $operationalModules = Module::whereIn('slug', $operationalModuleSlugs)
                ->whereIn('id', $enabledModuleIds)
                ->pluck('id')
                ->toArray();

            $managementModules = Module::whereIn('slug', $managementModuleSlugs)
                ->whereIn('id', $enabledModuleIds)
                ->pluck('id')
                ->toArray();

            $advancedModules = Module::whereIn('slug', $advancedModuleSlugs)
                ->whereIn('id', $enabledModuleIds)
                ->pluck('id')
                ->toArray();

            foreach ($globalRoles as $role) {
                $assignedModules = [];

                // Assign modules based on role name
                switch (strtolower($role->name)) {
                    case 'super administrator':
                    case 'administrator':
                        // Admins: All enabled modules
                        $assignedModules = $enabledModuleIds;
                        $this->command->info("  ✓ {$role->name}: ".count($assignedModules).' modules (all)');
                        break;

                    case 'user admin':
                        // User Admins: Basic + operational + management modules
                        $assignedModules = array_merge($basicModules, $operationalModules, $managementModules);
                        $this->command->info("  ✓ {$role->name}: ".count($assignedModules).' modules (up to management)');
                        break;

                    case 'user':
                        // Users: Basic + operational modules
                        $assignedModules = array_merge($basicModules, $operationalModules);
                        $this->command->info("  ✓ {$role->name}: ".count($assignedModules).' modules (basic + operational)');
                        break;

                    case 'client':
                    case 'viewer':
                        // Clients/Viewers: Only basic modules (read-only access)
                        // Clients/Viewers: Only basic modules (read-only access)
                        $assignedModules = $basicModules;
                        $this->command->info("  ✓ {$role->name}: ".count($assignedModules).' basic modules');
                        break;

                    default:
                        // Custom roles: Give basic modules by default
                        $assignedModules = $basicModules;
                        $this->command->info("  ✓ {$role->name} (custom): ".count($assignedModules).' basic modules');
                        break;
                }

                // Assign modules to role
                if (! empty($assignedModules)) {
                    $syncData = [];
                    foreach ($assignedModules as $moduleId) {
                        $syncData[$moduleId] = [
                            'organization_id' => $organization->id,
                            'has_access' => true,
                            'granted_by' => null,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    }

                    $role->modules()->sync($syncData);
                }
            }
        }

        $this->command->info("\n✅ Role-module access control configured successfully!");
        $this->command->info('📝 Summary:');
        $this->command->info('   - Clients: Basic modules only (Dashboard, Reports, Calendar)');
        $this->command->info('   - Users: Basic + Operational (CRM, Inventory, Projects, etc.)');
        $this->command->info('   - User Admins: Users + Management (HR, Accounting, Sales, etc.)');
        $this->command->info('   - Administrators: All enabled modules');
    }
}

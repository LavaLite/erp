<?php

namespace Database\Seeders;

use App\Models\Module;
use App\Models\Organization;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EnableModulesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $organizations = Organization::all();

        // Sample modules to enable for demo
        $moduleSlugs = ['dash', 'rpt', 'cal', 'crm', 'inv', 'proj', 'hr', 'acc', 'sale', 'purch', 'doc', 'chat', 'bi', 'auto', 'api', 'audit', 'payroll'];

        $modules = Module::whereIn('slug', $moduleSlugs)->get();

        $this->command->info("Enabling {$modules->count()} modules for {$organizations->count()} organizations...");

        foreach ($organizations as $org) {
            $enabled = 0;
            foreach ($modules as $module) {
                $exists = DB::table('organization_module')
                    ->where('organization_id', $org->id)
                    ->where('module_id', $module->id)
                    ->exists();

                if (! $exists) {
                    DB::table('organization_module')->insert([
                        'organization_id' => $org->id,
                        'module_id' => $module->id,
                        'is_enabled' => true,
                        'enabled_at' => now(),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    $enabled++;
                }
            }
            $this->command->info("  ✓ Enabled {$enabled} new modules for {$org->name}");
        }

        $this->command->info("\n✅ Modules enabled successfully!");
    }
}

<?php

namespace Database\Seeders;

use App\Models\Organization;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Seeder;

class TeamSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        echo "Seeding teams...\n";

        // Get organizations
        $organizations = Organization::all();

        if ($organizations->isEmpty()) {
            echo "No organizations found. Please run MultiOrganizationSeeder first.\n";

            return;
        }

        foreach ($organizations as $organization) {
            echo "Creating teams for {$organization->name}...\n";

            // Sales Team
            $salesTeam = Team::create([
                'organization_id' => $organization->id,
                'name' => 'Sales Team',
                'slug' => 'sales',
                'description' => 'Responsible for sales and customer acquisition',
                'color' => '#10B981', // Green
                'is_active' => true,
            ]);

            // Development Team
            $devTeam = Team::create([
                'organization_id' => $organization->id,
                'name' => 'Development Team',
                'slug' => 'dev',
                'description' => 'Product development and engineering',
                'color' => '#3B82F6', // Blue
                'is_active' => true,
            ]);

            // Frontend Sub-team
            $frontendTeam = Team::create([
                'organization_id' => $organization->id,
                'parent_team_id' => $devTeam->id,
                'name' => 'Frontend Team',
                'slug' => 'frontend',
                'description' => 'UI/UX development',
                'color' => '#6366F1', // Indigo
                'is_active' => true,
            ]);

            // Backend Sub-team
            $backendTeam = Team::create([
                'organization_id' => $organization->id,
                'parent_team_id' => $devTeam->id,
                'name' => 'Backend Team',
                'slug' => 'backend',
                'description' => 'API and database development',
                'color' => '#8B5CF6', // Purple
                'is_active' => true,
            ]);

            // Support Team
            $supportTeam = Team::create([
                'organization_id' => $organization->id,
                'name' => 'Support Team',
                'slug' => 'support',
                'description' => 'Customer support and success',
                'color' => '#F59E0B', // Amber
                'is_active' => true,
            ]);

            // Marketing Team
            $marketingTeam = Team::create([
                'organization_id' => $organization->id,
                'name' => 'Marketing Team',
                'slug' => 'marketing',
                'description' => 'Marketing and brand management',
                'color' => '#EC4899', // Pink
                'is_active' => true,
            ]);

            // HR Team
            $hrTeam = Team::create([
                'organization_id' => $organization->id,
                'name' => 'HR Team',
                'slug' => 'hr',
                'description' => 'Human resources and recruitment',
                'color' => '#EF4444', // Red
                'is_active' => true,
            ]);

            // Finance Team
            $financeTeam = Team::create([
                'organization_id' => $organization->id,
                'name' => 'Finance Team',
                'slug' => 'finance',
                'description' => 'Financial management and accounting',
                'color' => '#14B8A6', // Teal
                'is_active' => true,
            ]);

            // Add users to teams
            $users = $organization->users()->limit(5)->get();

            if ($users->isNotEmpty()) {
                // First user as team owner in multiple teams
                $salesTeam->addMember($users->first(), 'owner');
                $devTeam->addMember($users->first(), 'owner');

                // Distribute other users with various roles
                if ($users->count() > 1) {
                    $supportTeam->addMember($users->get(1), 'admin');
                    $marketingTeam->addMember($users->get(1), 'member');
                }

                if ($users->count() > 2) {
                    $frontendTeam->addMember($users->get(2), 'manager');
                    $devTeam->addMember($users->get(2), 'member');
                }

                if ($users->count() > 3) {
                    $backendTeam->addMember($users->get(3), 'manager');
                    $devTeam->addMember($users->get(3), 'member');
                }

                if ($users->count() > 4) {
                    $hrTeam->addMember($users->get(4), 'admin');
                    $financeTeam->addMember($users->get(4), 'billing');
                }
            }

            echo "  ✓ Created 8 teams for {$organization->name}\n";
        }

        $totalTeams = Team::count();
        echo "\n✓ Total teams created: {$totalTeams}\n";
        echo '✓ Teams with hierarchies: '.Team::whereNotNull('parent_team_id')->count()."\n";
        echo '✓ Root teams: '.Team::whereNull('parent_team_id')->count()."\n";
    }
}

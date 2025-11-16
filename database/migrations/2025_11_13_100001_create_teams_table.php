<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Teams table - organization-scoped teams
        Schema::create('teams', function (Blueprint $table) {
            $table->id();
            $table->uuid('organization_id');
            $table->foreignId('parent_team_id')->nullable()->constrained('teams')->onDelete('set null');
            $table->string('name');
            $table->string('slug')->index();
            $table->text('description')->nullable();
            $table->string('avatar')->nullable();
            $table->string('color', 7)->nullable(); // Hex color for UI
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->boolean('is_active')->default(true);
            $table->json('metadata')->nullable(); // For custom fields
            $table->timestamps();
            $table->softDeletes();

            // Foreign key constraint for organization_id
            $table->foreign('organization_id')->references('id')->on('organizations')->onDelete('cascade');

            // Unique constraint: slug per organization
            $table->unique(['organization_id', 'slug']);
        });

        // Team members pivot table
        Schema::create('team_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('role', ['owner', 'admin', 'manager', 'member', 'viewer', 'billing'])->default('member');
            $table->foreignId('invited_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('joined_at')->nullable();
            $table->timestamps();

            // Prevent duplicate team memberships
            $table->unique(['team_id', 'user_id']);
        });

        // Optional: Team module access (restrict modules per team)
        Schema::create('module_team', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained()->onDelete('cascade');
            $table->foreignId('module_id')->constrained()->onDelete('cascade');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['team_id', 'module_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('module_team');
        Schema::dropIfExists('team_user');
        Schema::dropIfExists('teams');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Creates roles and permissions tables with organization-scoping support.
     * Includes all pivot tables for RBAC functionality.
     */
    public function up(): void
    {
        // Create roles table
        Schema::create('roles', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
            $table->string('organization_id', 50)->nullable(); // String to support 'global'
            $table->string('name');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->timestamps();

            $table->unique(['organization_id', 'slug']);
            $table->index('organization_id');
        });

        // Create permissions table
        Schema::create('permissions', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
            $table->string('organization_id', 50)->nullable(); // String to support 'global'
            $table->string('name');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->timestamps();

            $table->unique(['organization_id', 'slug']);
            $table->index('organization_id');
        });

        // Create role_user pivot table (users have different roles per organization)
        Schema::create('role_user', function (Blueprint $table) {
            $table->id();
            $table->string('organization_id', 50)->nullable();
            $table->foreignId('role_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->timestamps();

            $table->unique(['organization_id', 'role_id', 'user_id']);
            $table->index('organization_id');
        });

        // Create permission_role pivot table (roles have permissions)
        Schema::create('permission_role', function (Blueprint $table) {
            $table->id();
            $table->foreignId('permission_id')->constrained()->onDelete('cascade');
            $table->foreignId('role_id')->constrained()->onDelete('cascade');
            $table->timestamps();

            $table->unique(['permission_id', 'role_id']);
        });

        // Create permission_user pivot table (users can have direct permissions)
        Schema::create('permission_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('permission_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->timestamps();

            $table->unique(['permission_id', 'user_id']);
        });

        // Create role_module pivot table (roles have module access)
        Schema::create('role_module', function (Blueprint $table) {
            $table->id();
            $table->foreignId('role_id')->constrained()->onDelete('cascade');
            $table->foreignId('module_id')->constrained()->onDelete('cascade');
            $table->uuid('organization_id');
            $table->boolean('has_access')->default(true)->comment('True = granted, False = explicitly denied');
            $table->foreignId('granted_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            // Foreign key constraint for organization_id
            $table->foreign('organization_id')->references('id')->on('organizations')->onDelete('cascade');

            // Prevent duplicate role-module assignments per organization
            $table->unique(['role_id', 'module_id', 'organization_id'], 'role_module_org_unique');

            // Indexes for performance
            $table->index(['role_id', 'organization_id']);
            $table->index(['module_id', 'organization_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('role_module');
        Schema::dropIfExists('permission_user');
        Schema::dropIfExists('permission_role');
        Schema::dropIfExists('role_user');
        Schema::dropIfExists('permissions');
        Schema::dropIfExists('roles');
    }
};

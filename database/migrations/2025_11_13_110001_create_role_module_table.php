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
    }
};

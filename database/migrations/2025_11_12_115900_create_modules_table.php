<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Creates the modules table for ERP module management.
     * Note: organization_module pivot table is created in the organizations migration.
     */
    public function up(): void
    {
        // Create modules table
        Schema::create('modules', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g., "Inventory Management"
            $table->string('slug')->unique(); // e.g., "inventory-management"
            $table->string('code')->unique(); // e.g., "INV" - short identifier
            $table->text('description')->nullable();
            $table->string('icon')->nullable(); // Icon identifier for UI
            $table->string('version')->default('1.0.0');
            $table->string('category')->nullable(); // e.g., "Finance", "HR", "Operations"
            $table->integer('display_order')->default(0); // For sorting in UI
            $table->json('dependencies')->nullable(); // Other module slugs this depends on
            $table->json('metadata')->nullable(); // Additional module-specific data
            $table->boolean('is_core')->default(false); // Core modules (always available)
            $table->boolean('is_active')->default(true); // Module availability
            $table->boolean('requires_license')->default(false); // Whether module needs license
            $table->timestamps();
            
            $table->index(['category', 'is_active']);
            $table->index('is_core');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('modules');
    }
};

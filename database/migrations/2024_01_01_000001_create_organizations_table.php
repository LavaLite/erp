<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * This table caches basic organization data from the Auth microservice.
     * The complete organization data lives in the Auth service.
     */
    public function up(): void
    {
        Schema::create('organizations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('domain')->unique()->nullable();
            $table->text('description')->nullable();
            $table->json('settings')->nullable();

            // Localization and regional settings
            $table->string('timezone')->default('UTC');
            $table->string('country')->nullable();
            $table->string('currency')->default('USD');

            $table->boolean('is_active')->default(true);

            // Subscription and billing fields
            $table->string('subscription_status')->default('trial');
            $table->integer('max_users')->nullable();
            $table->timestamp('trial_ends_at')->nullable();
            $table->string('subscription_id')->nullable()->unique();
            $table->string('plan_id')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('subscription_status');
            $table->index('subscription_id');
        });

        // Create organization_user pivot table (users can belong to multiple organizations)
        Schema::create('organization_user', function (Blueprint $table) {
            $table->id();
            $table->uuid('organization_id');
            $table->foreignId('user_id')->constrained();
            $table->boolean('is_active')->default(true);
            $table->timestamp('joined_at')->useCurrent();
            $table->timestamps();

            $table->foreign('organization_id')->references('id')->on('organizations')->onDelete('cascade');
            $table->unique(['organization_id', 'user_id']);
        });

        // Create organization_module pivot table
        Schema::create('organization_module', function (Blueprint $table) {
            $table->id();
            $table->uuid('organization_id');
            $table->foreignId('module_id')->constrained()->onDelete('cascade');
            $table->boolean('is_enabled')->default(true);
            $table->date('enabled_at')->nullable();
            $table->date('expires_at')->nullable(); // For subscription/license management
            $table->json('settings')->nullable(); // Module-specific settings per organization
            $table->json('limits')->nullable(); // Usage limits (e.g., max users, max records)
            $table->timestamps();

            $table->foreign('organization_id')->references('id')->on('organizations')->onDelete('cascade');
            $table->unique(['organization_id', 'module_id']);
            $table->index(['organization_id', 'is_enabled']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('organization_module');
        Schema::dropIfExists('organization_user');
        Schema::dropIfExists('organizations');
    }
};

<?php

namespace Database\Factories;

use App\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class OrganizationFactory extends Factory
{
    protected $model = Organization::class;

    public function definition(): array
    {
        $name = fake()->company();

        return [
            'id' => (string) Str::uuid(),
            'name' => $name,
            'slug' => Str::slug($name).'-'.fake()->unique()->numberBetween(1, 9999),
            'domain' => fake()->optional()->domainName(),
            'description' => fake()->optional()->sentence(),
            'settings' => [],
            'is_active' => true,
            'subscription_status' => 'active',
            'max_users' => 10,
            'trial_ends_at' => null,
            'subscription_id' => null,
            'plan_id' => null,
        ];
    }

    public function trial(): static
    {
        return $this->state(fn (array $attributes) => [
            'subscription_status' => 'trial',
            'trial_ends_at' => now()->addDays(14),
        ]);
    }

    public function suspended(): static
    {
        return $this->state(fn (array $attributes) => [
            'subscription_status' => 'suspended',
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}

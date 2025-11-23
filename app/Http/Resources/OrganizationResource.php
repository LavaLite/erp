<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrganizationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'domain' => $this->domain,
            'description' => $this->description,
            
            // Contact Information
            'email' => $this->email,
            'phone' => $this->phone,
            'website' => $this->website,
            
            // Address Information
            'address' => $this->address,
            'city' => $this->city,
            'state' => $this->state,
            'postal_code' => $this->postal_code,
            
            // Regional Settings
            'country' => $this->country,
            'timezone' => $this->timezone,
            'currency' => $this->currency,
            
            // Settings & Status
            'settings' => $this->settings,
            'is_active' => $this->is_active,
            
            // Subscription Information
            'subscription_status' => $this->subscription_status,
            'max_users' => $this->max_users,
            'trial_ends_at' => $this->trial_ends_at,
            
            // Timestamps
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}

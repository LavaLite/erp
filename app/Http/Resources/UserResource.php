<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'full_name' => $this->full_name,
            'email' => $this->email,
            'phone' => $this->phone,
            'date_of_birth' => $this->date_of_birth,
            'gender' => $this->gender,
            'city' => $this->city,
            'state' => $this->state,
            'country' => $this->country,
            'timezone' => $this->timezone,
            'language' => $this->language,
            'bio' => $this->bio,
            'avatar' => $this->avatar,
            'is_active' => $this->is_active,
            'email_verified_at' => $this->email_verified_at,
            'two_factor_enabled' => $this->two_factor_enabled,
            'two_factor_confirmed_at' => $this->two_factor_confirmed_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            // Include pivot data if available
            'pivot' => $this->when(isset($this->pivot), function () {
                return [
                    'organization_id' => $this->pivot->organization_id ?? null,
                    'created_at' => $this->pivot->created_at ?? null,
                ];
            }),

            // Include roles if loaded
            'roles' => RoleResource::collection($this->whenLoaded('roles')),

            // Include permissions if loaded
            'permissions' => PermissionResource::collection($this->whenLoaded('permissions')),

            // Include organizations if loaded
            'organizations' => OrganizationResource::collection($this->whenLoaded('organizations')),

            // Include teams if loaded
            'teams' => TeamResource::collection($this->whenLoaded('teams')),
        ];
    }
}

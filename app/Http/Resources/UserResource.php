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
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            // Include roles if loaded
            'roles' => RoleResource::collection($this->whenLoaded('roles')),

            // Include permissions if loaded
            'permissions' => PermissionResource::collection($this->whenLoaded('permissions')),

            // Include organizations if loaded
            'organizations' => OrganizationResource::collection($this->whenLoaded('organizations')),
        ];
    }
}

<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RoleResource extends JsonResource
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
            'description' => $this->description,
            'organization_id' => $this->organization_id,
            'is_global' => $this->organization_id === null || $this->organization_id === 'global',
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            // Include permissions if loaded
            'permissions' => PermissionResource::collection($this->whenLoaded('permissions')),
            'permissions_count' => $this->when(isset($this->permissions_count), $this->permissions_count),

            // Include organization if loaded
            'organization' => new OrganizationResource($this->whenLoaded('organization')),

            // Include users if loaded
            'users' => UserResource::collection($this->whenLoaded('users')),
            'users_count' => $this->when(isset($this->users_count), $this->users_count),

            // Include modules if loaded
            'modules' => ModuleResource::collection($this->whenLoaded('modules')),
            'modules_count' => $this->when(isset($this->modules_count), $this->modules_count),
        ];
    }
}

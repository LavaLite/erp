<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ModuleResource extends JsonResource
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
            'code' => $this->code,
            'description' => $this->description,
            'icon' => $this->icon,
            'version' => $this->version,
            'category' => $this->category,
            'display_order' => $this->display_order,
            'dependencies' => $this->dependencies,
            'metadata' => $this->metadata,
            'is_core' => $this->is_core,
            'is_active' => $this->is_active,
            'requires_license' => $this->requires_license,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            // Include pivot data if loaded (from organization relationship)
            'pivot' => $this->when($this->pivot, [
                'is_enabled' => $this->pivot->is_enabled ?? null,
                'enabled_at' => $this->pivot->enabled_at ?? null,
                'expires_at' => $this->pivot->expires_at ?? null,
                'settings' => $this->pivot->settings ?? null,
                'limits' => $this->pivot->limits ?? null,
            ]),
        ];
    }
}

<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TeamResource extends JsonResource
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
            'avatar' => $this->avatar,
            'color' => $this->color,
            'is_active' => $this->is_active,
            'organization_id' => $this->organization_id,
            'parent_team_id' => $this->parent_team_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            // Include pivot data if available (user's role in team)
            'pivot' => $this->when(isset($this->pivot), function () {
                return [
                    'role' => $this->pivot->role ?? null,
                    'joined_at' => $this->pivot->joined_at ?? null,
                    'invited_by' => $this->pivot->invited_by ?? null,
                ];
            }),

            // Include organization if loaded
            'organization' => new OrganizationResource($this->whenLoaded('organization')),

            // Include parent team if loaded
            'parent_team' => new TeamResource($this->whenLoaded('parentTeam')),

            // Include sub teams if loaded
            'sub_teams' => TeamResource::collection($this->whenLoaded('subTeams')),

            // Include members if loaded
            'members' => UserResource::collection($this->whenLoaded('users')),

            // Include counts if loaded
            'members_count' => $this->when(isset($this->users_count), $this->users_count),
            'modules_count' => $this->when(isset($this->modules_count), $this->modules_count),
        ];
    }
}

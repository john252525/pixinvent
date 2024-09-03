<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \Spatie\Permission\Models\Permission */
class PermissionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'guard_name' => $this->guard_name,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'permissions_count' => $this->permissions_count,
            'roles_count' => $this->roles_count,

            'permissions' => PermissionResource::collection($this->whenLoaded('permissions')),
            'roles' => RoleResource::collection($this->whenLoaded('roles')),
        ];
    }
}

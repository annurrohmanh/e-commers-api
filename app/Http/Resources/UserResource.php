<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                   => $this->id,
            'name'                 => $this->name,
            'username'             => $this->username,
            'email'                => $this->email,
            'email_verified_at'    => $this->email_verified_at,
            'phone'                => $this->phone,
            'address'              => $this->address,
            'profile_completed_at' => $this->profile_completed_at,
            'roles'                => RoleResource::collection($this->whenLoaded('roles')),
            'permissions'          => PermissionResource::collection($this->whenLoaded('permissions')),
            'catalogues'           => CatalogueResource::collection($this->whenLoaded('catalogues')),
        ];
    }
}

<?php

namespace App\Http\Resources\Admin;

use App\Models\LogErrors;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin LogErrors */
class LogErrorsResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'errors' => $this->errors,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            'user_id' => $this->user_id,

            'user' => new UsersResource($this->whenLoaded('user')),
        ];
    }
}

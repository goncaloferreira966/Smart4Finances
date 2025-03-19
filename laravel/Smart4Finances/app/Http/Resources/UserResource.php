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
            'name' => $this->name,
            'email' => $this->email,
            'type' => $this->type,
            'gender' => $this->gender,
            'nickname' => $this->nickname,
            'blocked' => $this->blocked,
            'deleted_at' => $this->deleted_at,
            'value' => $this->value,
            'photoFileName' => $this->photo_filename ? '/storage/photos/' . $this->photo_filename : null,
            'coin' => $this->coin,
            'email_verified_at' => $this->email_verified_at,
        ];
    }
}

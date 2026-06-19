<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ActivityLogResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'user_name' => $this->user_name,
            'user_role' => $this->user_role,
            'user_email' => $this->user_email,
            'action' => $this->action,
            'action_name' => $this->action_name,
            'action_category' => $this->action_category,
            'status' => $this->status,
            'description' => $this->readable_description,
            'ip_address' => $this->ip_address,
            'browser' => $this->browser,
            'device_type' => $this->device_type,
            'metadata' => $this->metadata,
            'resource_type' => $this->resource_type,
            'resource_id' => $this->resource_id,
            'affected_user_id' => $this->affected_user_id,
            'created_at' => optional($this->created_at)->toDateTimeString(),
        ];
    }
}

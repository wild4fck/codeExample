<?php

declare(strict_types=1);

namespace App\Http\Resources\Packages;

use App\Http\Resources\Agent\AgentWithUsersResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\Package
 */
class PackageResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param null|\Illuminate\Http\Request  $request
     *
     * @return array
     */
    public function toArray(?Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'status' => $this->status_id,
            'created_at' => $this->created_at,
            'period' => $this->period,
            'agent' => new AgentWithUsersResource($this->agent),
        ];
    }
}

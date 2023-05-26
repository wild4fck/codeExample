<?php

declare(strict_types=1);

namespace App\Http\Resources\Packages;

use App\Http\Resources\Agent\AgentSearchResource;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Services\PackageAvailableStatus\PackageAvailableStatusService;

/**
 * @mixin \App\Models\Package
 * @author Wild4fck <wild4fck@yandex.ru>
 */
class PackagesResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request  $request
     *
     * @return array
     * @throws \App\Exceptions\NoPackageTypeException
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'status' => $this->status_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'period' => $this->period,
            'agent' => new AgentSearchResource($this->agent),
            'actions' => PackageAvailableStatusService::getAvailablePackageActions($this->resource, Auth::user()),
        ];
    }
}

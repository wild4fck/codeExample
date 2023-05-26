<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Enums\AgreementTypeEnum;
use App\Enums\BankBranchClustersEnum;
use App\Enums\BankBranchStatusEnum;
use App\Enums\AgreementStatusEnum;
use App\Enums\OrganizationFormEnum;
use App\Enums\PackageStatusDirections;
use App\Enums\PackageStatusEnum;
use App\Enums\PackageTypeEnum;
use App\Enums\UserTypeEnum;
use App\Helpers\ResourceHelper;
use App\Http\Resources\Users\AuthUserResource;
use App\Models\User;
use App\Services\User\PermissionService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Набор данных, которые используются в целом для работы приложения.
 */
class AppDataResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request  $request
     *
     * @return array
     */
    public function toArray(Request $request): array
    {
        /** @var User $user */
        $user = $this->resource;
        return [
            'package' => [
                'statuses' => PackageStatusEnum::getNamedList('id'),
                'availableUserStatuses' => PermissionService::getStatusesFilteredByPermissions(),
                'types' => PackageTypeEnum::getNamedList(),
                'statusDirections' => PackageStatusDirections::getNamedList(),
            ],
        ];
    }
}

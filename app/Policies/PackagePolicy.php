<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\PackageStatusEnum;
use App\Models\Package;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class PackagePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models
     *
     * @param \App\Models\User  $user
     *
     * @return Response
     */
    public function viewAny(User $user): Response
    {
        if (User::isUserBankEmployee($user) && !$user->can('sections.documents')) {
            $this->deny(__('policy.deny_section'));
        }

        return $this->allow();
    }

    /**
     * Determine whether the user can view model
     *
     * @param \App\Models\User  $user
     * @param \App\Models\Package  $package
     *
     * @return Response
     */
    public function view(User $user, Package $package): Response
    {
        if (User::isUserAgent($user) && $package->agent->id !== $user->agent->id) {
            return $this->deny(__('policy.deny_package'));
        } 

        return $this->allow();
    }

    /**
     * Determine whether the user can change status
     *
     * @param \App\Models\User  $user
     * @param \App\Models\Package  $package
     *
     * @return Response
     */
    public function changeStatus(User $user, Package $package): Response
    {
        if (!User::isUserBankEmployee($user)) {
            return $this->allow();
        }

        // Проверка доступа по статусам
        $status = PackageStatusEnum::getNameById($package->status_id);
        if (!$user->can("statuses.$package->type.$status")) {
            $this->deny(__('policy.deny_action'));
        }

        return $this->allow();
    }
}

<?php

declare(strict_types=1);

namespace App\Repository;

use App\Enums\OrganizationFormEnum;
use App\Enums\PackageStatusEnum;
use App\Models\Package;
use App\Models\Status;
use App\Models\User;
use App\Services\PackageTypes\PackageTypeService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * @author Wild4fck <wild4fck@yandex.ru>
 */
class PackageRepository extends BaseAbstract
{
    public function __construct(Package $model)
    {
        parent::__construct($model);
    }

    /**
     * TODO объединить в билдер
     *
     * @param array|null  $data
     *
     * @return LengthAwarePaginator
     */
    public function allPaginate(array $data = null): LengthAwarePaginator
    {
        $this->indexQuery($data);
        if (isset($data['filters']['status']) && $data['filters']['status'] !== 0) {
            $this->query->where('packages.status_id', $data['filters']['status']);
        }

        $select = $groupBy = [
            'packages.id',
            'packages.type',
            'packages.status_id',
            'packages.created_at',
            'packages.updated_at',
            'packages.agent_id',
            'packages.period',
        ];

        $this->query->select($select)->groupBy($groupBy);
        return $this->query->paginate(
            $data['pagination']['count'] ?? 20,
            ['*'],
            'page',
            $data['pagination']['page'] ?? 1
        );
    }

    /**
     * Инициализация запроса для главной страницы
     *
     * @param array|null  $data
     */
    private function indexQuery(array $data = null): Builder
    {
        $this->query->from('packages')
            ->join('agents', 'agents.id', '=', 'packages.agent_id')
            ->join('agent_user', 'agents.id', '=', 'agent_user.agent_id')
            ->join('users', 'agent_user.user_id', '=', 'users.id')
            ->leftJoin('documents', function ($query) use ($data) {
                $query->on('documents.package_id', '=', 'packages.id');
            });

        /** @var User $user */
        $user = Auth::user();

        if (User::isUserAgent($user)) {
            $this->query->where('packages.agent_id', $user->agent->id);
            $this->query->whereNotIn('packages.status_id', [PackageStatusEnum::DRAFT, PackageStatusEnum::CANCELED]);
        } else {
            $this->query->whereIn('packages.status_id', Status::bankEmployeePermissions($user)->pluck('id')->toArray());
        }

        // todo: осознать как правильно фильтровать для агентов
        /*$userPackageTypes = (new PackageTypeService($user))->getTypesForUser()['hasAccess'];
        if (!isset($data['filters']['packageType'])) {
            $this->query->whereIn('packages.type', $userPackageTypes);
        }*/

        if (isset($data['filters'])) {
            $this->applyFilterToIndexQuery($data['filters']);
        }

        if (isset($data['sort'])) {
            $this->query->orderBy($data['sort']['order_by'], $data['sort']['direction']);
        } else {
            $this->query->orderByDesc('packages.id');
        }

        return $this->query;
    }

    /**
     * Применить фильтр к основному запросу
     *
     * @param array  $filters
     *
     * @return void
     */
    private function applyFilterToIndexQuery(array $filters): void
    {
        if (isset($filters['id'])) {
            $this->query->where('documents.id', $filters['id']);
        }

        if (isset($filters['packageType'])) {
            $userPackageTypes = (new PackageTypeService(Auth::user()))->getTypesForUser()['hasAccess'];
            $packageTypes = collect($filters['packageType'])
                ->filter(fn($item) => in_array($item, $userPackageTypes, true))
                ->toArray();
            $this->query->whereIn('packages.type', $packageTypes);
        }

        if (isset($filters['name'])) {
            $this->query->where('documents.name', 'ILIKE', "%{$filters['name']}%");
        }

        $this->query->filterByAgent($filters['agentName']);

        if (isset($filters['period'])) {
            $this->query->where('packages.period', '=', $filters['period']);
        }
    }

    /**
     * Получение данных счетчиков для главной страницы
     *
     * @param array|null  $data
     */
    public function allCounter(array $data = null): Collection
    {
        $query = $this->indexQuery($data);
        $result = DB::query()
            ->from(
                $query->select('packages.status_id')
                    ->groupBy(['packages.id', 'packages.status_id'])
                    ->toBase(),
                'st'
            )
            ->select('status_id', DB::raw('COUNT(*) AS count'))
            ->groupBy(['status_id'])
            ->pluck('count', 'status_id');

        $result[0] = $result->sum();

        return $result;
    }
}

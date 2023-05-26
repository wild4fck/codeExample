<?php

declare(strict_types=1);

namespace App\Models;

use App\Interfaces\PackageInfoInterface;
use App\Models\Traits\AgentSearch;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * \App\Models\Package
 *
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string $type Тип пакета
 * @property int|null $status_id Статус пакета
 * @property int $agent_id Агент назначенный на пакет
 * @property int $bank_user_id Сотрудник назначенный на пакет
 * @property string $period Период
 * @property-read \App\Models\Agent $agent
 * @property-read \App\Models\User $bankUser
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Document> $documents
 * @property-read int|null $documents_count
 * @method static Builder|Package newModelQuery()
 * @method static Builder|Package newQuery()
 * @method static Builder|Package query()
 * @method static Builder|Package whereAgentId($value)
 * @method static Builder|Package whereBankUserId($value)
 * @method static Builder|Package whereCreatedAt($value)
 * @method static Builder|Package whereId($value)
 * @method static Builder|Package wherePeriod($value)
 * @method static Builder|Package whereStatusId($value)
 * @method static Builder|Package whereType($value)
 * @method static Builder|Package whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Package extends Model implements PackageInfoInterface
{
    use HasFactory;
    use AgentSearch;

    protected $fillable = [
        'type',
        'status_id',
        'agent_id',
        'bank_user_id',
        'period',
    ];

    /**
     * Получение пользователя агента, назначенного на пакет
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function agent(): BelongsTo
    {
        return $this->belongsTo(Agent::class, 'agent_id', 'id');
    }

    /**
     * Получение пользователя банка, назначенного на пакет
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function bankUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'bank_user_id', 'id');
    }

    /**
     * Документы пакета
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }

    /** @inheritDoc */
    public function getPackageId(): ?int
    {
        return $this->id;
    }
}

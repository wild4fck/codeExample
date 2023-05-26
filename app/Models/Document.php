<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\UserTypeEnum;
use App\Interfaces\PackageInfoInterface;
use App\Models\Interfaces\CommentableInterface;
use App\Models\Traits\Commentable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * \App\Models\Document
 *
 * @property int $id
 * @property int $package_id Id пакета документов
 * @property string|null $name Наименование загруженного документа
 * @property string|null $slot Название слота из PackageOfDocuments
 * @property string $disk Указывается название диска из config.filesystems для корректной работы путей
 * @property string|null $path Путь до оригинала файла
 * @property string|null $sig_path Путь до подписанного файла
 * @property string $cabinet К какому кабинету относится документ
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Comment> $comments
 * @property-read int|null $comments_count
 * @property-read \App\Models\Package $package
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Signature> $signatures
 * @property-read int|null $signatures_count
 * @method static \Illuminate\Database\Eloquent\Builder|Document newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Document newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Document onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Document query()
 * @method static \Illuminate\Database\Eloquent\Builder|Document whereCabinet($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Document whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Document whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Document whereDisk($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Document whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Document whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Document wherePackageId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Document wherePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Document whereSigPath($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Document whereSlot($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Document whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Document withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Document withoutTrashed()
 * @mixin \Eloquent
 */
class Document extends Model implements PackageInfoInterface, CommentableInterface
{
    use HasFactory;
    use SoftDeletes;
    use Commentable;

    protected $fillable = [
        'package_id',
        'name',
        'slot',
        'disk',
        'path',
        'sig_path',
        'cabinet',
    ];

    /**
     * Получить пакет документа
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function signatures(): HasMany
    {
        return $this->hasMany(Signature::class);
    }

    /**
     * @return bool
     */
    public function isForBankCabinet(): bool
    {
        return $this->cabinet === UserTypeEnum::BANK_EMPLOYEE;
    }

    /**
     * @return bool
     */
    public function isForAgentCabinet(): bool
    {
        return $this->cabinet === UserTypeEnum::AGENT;
    }

    /** @inheritDoc */
    public function getPackageId(): int
    {
        return $this->package->id;
    }

    /**
     * @return null|\App\Models\Signature
     */
    public function bankSignature(): ?Signature
    {
        return $this->signatureByUserType(UserTypeEnum::BANK_EMPLOYEE);
    }

    /**
     * Получение простой подписи документа по типу пользователя
     *
     * @param string  $userType
     *
     * @return null|\App\Models\Signature
     */
    private function signatureByUserType(string $userType): ?Signature
    {
        return Signature::query()
            ->select('signatures.*')
            ->join('users', 'users.id', '=', 'signatures.user_id')
            ->where('signatures.document_id', $this->id)
            ->where('users.type', $userType)
            ->orderByDesc('signatures.id')
            ->first();
    }

    /**
     * @return null|\App\Models\Signature
     */
    public function agentSignature(): ?Signature
    {
        return $this->signatureByUserType(UserTypeEnum::AGENT);
    }
}

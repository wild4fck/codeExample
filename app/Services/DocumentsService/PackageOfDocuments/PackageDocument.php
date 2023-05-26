<?php

declare(strict_types=1);

namespace App\Services\DocumentsService\PackageOfDocuments;

use App\Enums\UserTypeEnum;
use Illuminate\Support\Carbon;

class PackageDocument
{
    /**
     * Id документа
     *
     * @see \App\Models\Document
     * @var null|int
     */
    public ?int $id = null;

    /**
     * Русскоязычное наименование слота
     *
     * @var null|string
     */
    public ?string $title = null;

    /**
     * Наименование документа
     *
     * @see \App\Models\Document
     * @var null|string
     */
    public ?string $name = null;

    /**
     * Наименование слота документа
     *
     * @var null|string
     */
    public ?string $slot = null;

    /**
     * Путь до файла на диске
     *
     * @see \App\Models\Document
     * @var null|string
     */
    public ?string $path = null;

    /**
     * Возможные расширения загружаемых файлов
     *
     * @var null|string
     */
    public ?string $extension = null;

    /**
     * Флаг - загружен ли документ (наличие записи в базе)
     *
     * @var null|bool
     */
    public ?bool $uploaded = false;

    /**
     * Флаг - нужно ли подписывать со стороны банка
     *
     * @var null|bool
     */
    public ?bool $need_to_sign_bank = false;

    /**
     * Флаг - нужно ли подписывать со стороны Агента
     *
     * @var null|bool
     */
    public ?bool $need_to_sign_agent = false;

    /**
     * Дата подписания со стороны банка
     *
     * @see \App\Models\Document
     * @var null|\Illuminate\Support\Carbon
     */
    public ?Carbon $bank_sig_date = null;

    /**
     * Дата подписания со стороны Агента
     *
     * @see \App\Models\Document
     * @var null|\Illuminate\Support\Carbon
     */
    public ?Carbon $agent_sig_date = null;

    /**
     * Флаг - является ли документ обязательным
     *
     * @var null|bool
     */
    public ?bool $is_required = false;

    /**
     * Список подписей по документам
     *
     * @see \App\Models\Document
     * @see \App\Models\Signature
     * @var null|array
     */
    public ?array $signatures = null;
    /**
     * Правила описывающие на каких статусах производится подписание стороной
     *
     * @example sign_statuses[тип стороны][статус1, статус2,...]
     * @var null|array
     */
    public ?array $sign_statuses = null;

    /**
     * Правила описывающие на каких статусах сторона может редактировать документ
     * Для банка - проверка прав, для агента - список статусов
     *
     * @example Банк editing[UserTypeEnum::BANK_EMPLOYEE][разрешение1, разрешение2,...]
     * @example Агент editing[UserTypeEnum::BANK_AGENT][статус1, статус2,...]
     * @var null|array
     */
    public ?array $editing = null;

    /**
     * Правила описывающие на каких статусах сторона может загружать документ
     * Для банка - проверка прав, для агента - список статусов
     *
     * @example Банк can_upload[UserTypeEnum::BANK_EMPLOYEE][разрешение1, разрешение2,...]
     * @example Агент can_upload[UserTypeEnum::AGENT][статус1, статус2,...]
     * @var null|array
     */
    public ?array $can_upload = null;

    /**
     * Список типов пользователей, для которых скрыт документ
     *
     * @var null|array
     */
    public ?array $hide = null;

    /**
     * Дата создания документа
     *
     * @see \App\Models\Document
     * @var null|Carbon
     */
    public ?Carbon $created_at = null;

    /**
     * Доступно ли комментирование для документа
     *
     * @var null|bool
     */
    public ?bool $commenting = null;

    /**
     * К какому кабинету относится документа (агент или банк)
     *
     * @var string
     */
    public string $cabinet = UserTypeEnum::BANK_EMPLOYEE;

    /**
     * @param null|int  $id
     *
     * @return PackageDocument
     */
    public function setId(?int $id): PackageDocument
    {
        $this->id = $id;
        return $this;
    }

    /**
     *
     * @param null|string  $title {@see $title}
     *
     * @return PackageDocument
     */
    public function setTitle(?string $title): PackageDocument
    {
        $this->title = $title;
        return $this;
    }

    /**
     * @param null|string  $name
     *
     * @return PackageDocument
     */
    public function setName(?string $name): PackageDocument
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @param null|string  $slot
     *
     * @return PackageDocument
     */
    public function setSlot(?string $slot): PackageDocument
    {
        $this->slot = $slot;
        return $this;
    }

    /**
     * @param null|string  $path
     *
     * @return PackageDocument
     */
    public function setPath(?string $path): PackageDocument
    {
        $this->path = $path;
        return $this;
    }

    /**
     * @param null|string  $extension
     *
     * @return PackageDocument
     */
    public function setExtension(?string $extension): PackageDocument
    {
        $this->extension = $extension;
        return $this;
    }

    /**
     * @param null|bool  $uploaded
     *
     * @return PackageDocument
     */
    public function setUploaded(?bool $uploaded): PackageDocument
    {
        $this->uploaded = $uploaded;
        return $this;
    }

    /**
     * @param null|bool  $need_to_sign_bank
     *
     * @return PackageDocument
     */
    public function setNeedToSignBank(?bool $need_to_sign_bank): PackageDocument
    {
        $this->need_to_sign_bank = $need_to_sign_bank;
        return $this;
    }

    /**
     * @param null|bool  $need_to_sign_agent
     *
     * @return PackageDocument
     */
    public function setNeedToSignAgent(?bool $need_to_sign_agent): PackageDocument
    {
        $this->need_to_sign_agent = $need_to_sign_agent;
        return $this;
    }

    /**
     * @param null|string  $bank_sig_date
     *
     * @return PackageDocument
     */
    public function setBankSigDate(?string $bank_sig_date): PackageDocument
    {
        $this->bank_sig_date = $bank_sig_date;
        return $this;
    }

    /**
     * @param null|string  $agent_sig_date
     *
     * @return PackageDocument
     */
    public function setAgentSigDate(?string $agent_sig_date): PackageDocument
    {
        $this->agent_sig_date = $agent_sig_date;
        return $this;
    }

    /**
     * @param null|bool  $is_required
     *
     * @return PackageDocument
     */
    public function setIsRequired(?bool $is_required = true): PackageDocument
    {
        $this->is_required = $is_required;
        return $this;
    }

    /**
     * @param null|array  $signatures
     *
     * @return PackageDocument
     */
    public function setSignatures(?array $signatures): PackageDocument
    {
        $this->signatures = $signatures;
        return $this;
    }

    /**
     * @param null|array  $sign_statuses
     *
     * @return PackageDocument
     */
    public function setSignStatuses(?array $sign_statuses): PackageDocument
    {
        $this->sign_statuses = $sign_statuses;
        return $this;
    }

    /**
     * @param null|array  $editing
     *
     * @return PackageDocument
     */
    public function setEditing(?array $editing): PackageDocument
    {
        $this->editing = $editing;
        return $this;
    }

    /**
     * @param null|array  $can_upload
     *
     * @return PackageDocument
     */
    public function setCanUpload(?array $can_upload): PackageDocument
    {
        $this->can_upload = $can_upload;
        return $this;
    }

    /**
     * @param null|array  $hide
     *
     * @return PackageDocument
     */
    public function setHide(?array $hide): PackageDocument
    {
        $this->hide = $hide;
        return $this;
    }

    /**
     * @param null|string  $created_at
     *
     * @return PackageDocument
     */
    public function setCreatedAt(?string $created_at): PackageDocument
    {
        $this->created_at = $created_at;
        return $this;
    }

    /**
     * @param null|bool  $commenting
     *
     * @return PackageDocument
     */
    public function setCommenting(?bool $commenting = true): PackageDocument
    {
        $this->commenting = $commenting;
        return $this;
    }

    /**
     * @param string  $cabinet
     *
     * @return PackageDocument
     */
    public function setCabinet(string $cabinet): PackageDocument
    {
        $this->cabinet = $cabinet;
        return $this;
    }
}

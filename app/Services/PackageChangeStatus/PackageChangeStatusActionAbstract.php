<?php

declare(strict_types=1);

namespace App\Services\PackageChangeStatus;

use App\Enums\PackageStatusDirections;
use App\Enums\PackageStatusEnum;
use App\Enums\UserTypeEnum;
use App\Exceptions\NoPackageTypeException;
use App\Mail\Package\PackageStatusChanged;
use App\Models\Package;
use App\Models\User;
use App\Services\DocumentsService\PackageOfDocuments\PackageDocumentsService;
use App\Services\PackageChangeStatus\Exceptions\PackageStatusValidateException;
use App\Services\PackageChangeStatus\Exceptions\UnexpectedStatusChangeException;
use App\Services\PackageServicesFactory\PackageServicesFactory;
use Auth;
use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Этот класс представляет собой абстрактный класс,
 * содержащий общую функциональность для изменения статуса пакета и определение действий,
 * необходимых для перехода на новый статус.
 * Также в этом классе определены методы валидации необходимых документов и подписей
 * для перехода на новый статус пакета.
 *
 * @author Wild4fck <wild4fck@yandex.ru>
 */
abstract class PackageChangeStatusActionAbstract implements PackageChangeStatusActionInterface
{
    /**
     * Текущий пакет документов, в котором меняем статус
     *
     * @var \App\Models\Package
     */
    protected Package $package;

    /**
     * Автоматический переход или ручной
     *
     * @var bool
     */
    protected bool $isAutoChange;

    /**
     * В каком направлении производится перевод статуса
     *
     * @var null|string
     */
    protected ?string $direction;

    /**
     * @param \App\Models\Package  $package
     * @param bool  $isAutoChange
     *
     * @throws \App\Exceptions\NoPackageTypeException
     * @throws \App\Services\PackageChangeStatus\Exceptions\PackageStatusValidateException
     * @throws \App\Services\PackageChangeStatus\Exceptions\UnexpectedStatusChangeException
     * @throws \JsonException
     */
    public function __construct(Package $package, bool $isAutoChange = false)
    {
        $this->package = $package;
        $this->isAutoChange = $isAutoChange;
        $this->isInvalidTransition();

        $this->direction = PackageServicesFactory::getServices($package->type)
                ->createPackageAvailableStatusesService($package)
                ->getMap()[$package->status_id]['actions'][$this::class];

        if ($this->direction !== PackageStatusDirections::BACKWARD) {
            $this->validate();
        }
    }

    /**
     * Общая проверка на корректность перехода из одного статуса в другой
     *
     * @return void
     * @throws \App\Services\PackageChangeStatus\Exceptions\UnexpectedStatusChangeException
     * @throws \App\Services\PackageChangeStatus\Exceptions\PackageStatusValidateException
     */
    protected function isInvalidTransition(): void
    {
        if (!Auth::check()) {
            return;
        }
        $this->isInvalidTransitionForBankEmployee();
        $this->isInvalidTransitionForAgent();
    }

    /**
     * @return void
     * @throws \App\Services\PackageChangeStatus\Exceptions\UnexpectedStatusChangeException
     */
    private function isInvalidTransitionForBankEmployee(): void
    {
        if (User::isUserBankEmployee()
            && !in_array($this->package->status_id, static::getAvailableFromForBankEmployee(), true)) {
            throw new UnexpectedStatusChangeException(
                'Переход пакета на запрошенный статус из текущего не предусмотрен ('
                . PackageStatusEnum::getText($this->package->status_id)
                . '->'
                . PackageStatusEnum::getText(static::getStatus()) . ').'
            );
        }
    }

    /**
     * @return void
     * @throws \App\Services\PackageChangeStatus\Exceptions\PackageStatusValidateException
     */
    private function isInvalidTransitionForAgent(): void
    {
        if (User::isUserAgent() && !in_array($this->package->status_id, static::getAvailableFromForAgent(), true)) {
            throw new PackageStatusValidateException(
                sprintf(
                    'Переход "%s" -> "%s" недоступен для агента',
                    PackageStatusEnum::getText($this->package->status_id),
                    PackageStatusEnum::getText(static::getStatus())
                )
            );
        }
    }

    /**
     * Получить название для кнопки на фронте
     *
     * @param int  $status
     *
     * @return string
     */
    public static function getActionTitle(int $status): string
    {
        return static::getActionTitles()[$status] ?? PackageStatusEnum::getText(static::getStatus());
    }

    /**
     * Получить сообщение о смене статуса
     *
     * @param int  $status
     *
     * @return string
     */
    public static function getActionMessage(int $status): string
    {
        if (is_array(static::getActionMessages())) {
            return static::getActionMessages()[$status] ?? static::getDefaultActionMessage();
        }

        return static::getActionMessages() ?? static::getDefaultActionMessage();
    }

    /**
     * Дефолтное сообщение о смене статуса
     *
     * @return string
     */
    private static function getDefaultActionMessage(): string
    {
        $statusTitle = PackageStatusEnum::getText(static::getStatus());
        return "Статус успешно изменён на \"{$statusTitle}\"";
    }

    /**
     * @return void
     * @throws \App\Exceptions\NoPackageTypeException
     * @throws \App\Services\PackageChangeStatus\Exceptions\PackageStatusValidateException
     * @throws \JsonException
     */
    protected function checkNeededDocuments(): void
    {
        $pod = PackageDocumentsService::createPackage($this->package)->fillFromDatabase()->toArray();

        $unloadedDocuments = array_filter($pod, static function ($doc) {
            return $doc['is_required'] === true && $doc['uploaded'] === false;
        });
        unset($pod);

        if (!empty($unloadedDocuments)) {
            $message = [];
            $necessaryActions = [];
            foreach ($unloadedDocuments as $doc) {
                $message[] = "Не загружен документ \"{$doc['title']}\"";
                $necessaryActions[$doc['slot']] = [
                    'reason' => 'upload',
                    'message' => 'Требуется загрузка документа',
                ];
            }
            throw new PackageStatusValidateException(implode("\n", array_unique($message)), $necessaryActions);
        }
    }

    /**
     * @param string  $userType
     *
     * @return void
     * @throws \App\Exceptions\NoPackageTypeException
     * @throws \App\Services\PackageChangeStatus\Exceptions\PackageStatusValidateException
     * @throws \JsonException
     */
    protected function checkNeededToSign(string $userType): void
    {
        $sigParam = match ($userType) {
            UserTypeEnum::BANK_EMPLOYEE => [
                'sigDateParam' => 'bank_sig_date',
                'needToSigParam' => 'need_to_sign_bank',
            ],
            UserTypeEnum::AGENT => [
                'sigDateParam' => 'agent_sig_date',
                'needToSigParam' => 'need_to_sign_agent',
            ],
        };

        $pod = PackageDocumentsService::createPackage($this->package)->fillFromDatabase()->toArray();

        $unsignedDocuments = array_filter($pod, static function ($doc) use ($sigParam) {
            if (!$doc['is_required'] && !$doc['uploaded']) {
                return false;
            }
            return empty($doc[$sigParam['sigDateParam']]) && $doc[$sigParam['needToSigParam']];
        });
        unset($pod, $sigParam);

        if (!empty($unsignedDocuments)) {
            $message = [];
            $necessaryActions = [];
            foreach ($unsignedDocuments as $doc) {
                $message[] = "Не подписан документ \"{$doc['title']}\"";
                if (!isset($necessaryActions[$doc['slot']])) {
                    $necessaryActions[$doc['slot']] = [
                        'documentsList' => [],
                        'reason' => 'sign',
                        'message' => 'Требуется подписание документа',
                    ];
                }
                $necessaryActions[$doc['slot']]['documentsList'][] = $doc['id'];
            }

            throw new PackageStatusValidateException(implode("\n", array_unique($message)), $necessaryActions);
        }
    }

    /**
     * @return null|int
     */
    public function updateStatus(): ?int
    {
        return $this->getStatusAfter();
    }

    /** @inheritDoc */
    public function getStatusAfter(): ?int
    {
        return null;
    }

    /**
     * Уведомление персонала о смене статуса
     *
     * @param User|array<User>  $to
     * @param bool  $isForBank
     *
     * @return void
     */
    protected function notifyUsersAboutPackageStatusChange(User|array $to, bool $isForBank = false): void
    {
        if (empty($to)) {
            return;
        }

        try {
            if (!static::getStatus()) {
                return;
            }

            foreach (Arr::wrap($to) as $recipient) {
                Mail::to($recipient)->send(
                    new PackageStatusChanged($this->package, static::getStatus(), $isForBank, $recipient)
                );
            }
        } catch (Exception $exception) {
            Log::error('Уведомление о смене статуса', [
                'exception' => $exception,
            ]);
        }
    }

    /**
     * Валидация для каждого отдельного статуса
     *
     * @return void
     * @throws PackageStatusValidateException
     * @throws NoPackageTypeException
     * @throws \JsonException
     */
    abstract protected function validate(): void;
}

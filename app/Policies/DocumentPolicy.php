<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;
use App\Models\Package;
use App\Models\Document;
use Illuminate\Auth\Access\Response;
use Illuminate\Auth\Access\HandlesAuthorization;
use App\Services\DocumentsService\PackageOfDocuments\PackageDocumentsService;

class DocumentPolicy
{
    use HandlesAuthorization;

    /**
     * Выполнить предварительную авторизацию.
     *
     * @param \App\Models\User  $user
     *
     * @return null|bool
     */
    public function before(User $user): ?bool
    {
        if (User::isUserAgent($user) && !$user->agent->isActive()) {
            return false;
        }

        return null;
    }

    /**
     * Determine whether the user can create comment for document.
     *
     * @param \App\Models\User  $user
     * @param \App\Models\Document  $document
     *
     * @return \Illuminate\Auth\Access\Response
     */
    public function addComment(User $user, Document $document): Response
    {
        if ($user->can('documents.comments_write')
            || (User::isUserAgent($user)
                && $document->package->agent->users->contains(fn(User $item) => $item->id === $user->id))
        ) {
            return $this->allow();
        }

        return $this->deny(__('policy.deny_comment_add'));
    }

    /**
     * Determine whether the user can get comments for document.
     *
     * @param \App\Models\User  $user
     * @param \App\Models\Document  $document
     *
     * @return \Illuminate\Auth\Access\Response
     */
    public function getComments(User $user, Document $document): Response
    {
        if ($user->canAny(["documents.comments_read", "documents.comments_write"])
            || (User::isUserAgent($user)
                && $document->package->agent->users->contains(fn(User $item) => $item->id === $user->id))
        ) {
            return $this->allow();
        }

        return $this->deny(__('policy.deny_comment_get'));
    }

    /**
     * Может ли пользователь загружать документ
     *
     * @param \App\Models\User  $user
     *
     * @return \Illuminate\Auth\Access\Response
     * @throws \App\Exceptions\NoPackageTypeException
     */
    public function store(User $user): Response
    {
        /** @var Package $package */
        $package = request()->package ?? null;
        $slot = request('slot');

        if (!$package || !$slot) {
            return $this->deny(__('policy.deny_document_upload'));
        }

        $documentSlot = PackageDocumentsService::createPackage($package, [
            'user' => $user
        ])->getDocumentBySlot($slot);

        return in_array($package->status_id, $documentSlot->can_upload ?? [], true)
            ? $this->allow()
            : $this->deny(__('policy.deny_document_upload'));
    }

    /**
     * Может ли пользователь удалять\обновлять документ
     *
     * @param \App\Models\User  $user
     * @param \App\Models\Document  $document
     *
     * @return \Illuminate\Auth\Access\Response
     * @throws \App\Exceptions\NoPackageTypeException
     */
    public function updateOrDelete(User $user, Document $document): Response
    {
        $documentSlot = PackageDocumentsService::createPackage($document->package, [
            'user' => $user
        ])->getDocumentBySlot($document->slot);

        return in_array($document->package->status_id, $documentSlot->editing ?? [], true)
            ? $this->allow()
            : $this->deny(__('policy.deny_document_delete'));
    }

    /**
     * Может ли пользователь подписывать документ
     *
     * @param \App\Models\User  $user
     * @param \App\Models\Document  $document
     *
     * @return \Illuminate\Auth\Access\Response
     */
    public function sign(User $user, Document $document): Response
    {
        if (User::isUserBankEmployee($user) && !$user->can("documents.{$document->package->type}.signature")) {
            return $this->deny(__('policy.deny_action'));
        }

        return ($document->package->agent->users->contains('id', $user->id)
            || $document->package->bank_user_id === $user->id)
            ? $this->allow()
            : $this->deny(__('policy.deny_action'));
    }

    /**
     * Может ли пользователь скачать оригинал, если существует подпись
     *
     * @param \App\Models\User  $user
     * @param \App\Models\Document  $document
     *
     * @return \Illuminate\Auth\Access\Response
     */
    public function download(User $user, Document $document): Response
    {
        if (User::isUserBankEmployee($user)
            && !empty($document->sig_path)
            && !$user->can('documents.download_original')
        ) {
            return $this->deny(__('policy.deny_action'));
        }

        return $this->allow();
    }
}

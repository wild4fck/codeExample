<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\LoggerDataTypeEnum;
use App\Http\Requests\Comment\CommentIndexRequest;
use App\Http\Requests\Comment\CommentStoreRequest;
use App\Http\Requests\Documents\DocumentDownloadRequest;
use App\Http\Requests\Documents\DocumentStoreRequest;
use App\Http\Requests\Documents\DocumentUploadRequest;
use App\Http\Requests\Documents\UpdateDocumentRequest;
use App\Http\Resources\Comment\CommentResource;
use App\Http\Resources\Signature\SignatureResource;
use App\Models\Comment;
use App\Models\Document;
use App\Models\Package;
use App\Models\PackageLogger;
use App\Repository\DocumentRepository;
use App\Services\Comment\Catalog\DocumentCommentator;
use App\Services\DocumentsService\PackageOfDocuments\PackageDocumentsService;
use App\Services\ModelFileService\ModelFileServiceFactory;
use App\Services\PackageAvailableStatus\Exceptions\ForbiddenPackageStatusException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Log;
use Sign;
use Throwable;
use Commentator;

class DocumentController extends Controller
{

    /**
     * Загрузка файлов с созданием в базе записей документа (создание)
     *
     * @param \App\Models\Package  $package
     * @param \App\Http\Requests\Documents\DocumentStoreRequest $request
     *
     * @return JsonResponse
     * @throws \App\Exceptions\NoPackageTypeException
     * @throws \Exception|\Throwable
     */
    public function store(Package $package, DocumentStoreRequest $request): JsonResponse
    {
        try {
            DB::beginTransaction();
            $document = Document::create(
                [
                    'package_id' => $package->id,
                    'cabinet' => \Auth::user()->type,
                    'slot' => $request->slot,
                    'name' => $request->file->getClientOriginalName(),
                    'disk' => 'local',
                ]
            );
            $document = ModelFileServiceFactory::get($document)->uploadFile($request->file);
            DB::commit();
        } catch (Throwable $exception) {
            DB::rollBack();
            $message = 'Ошибка загрузки документа';
            Log::error($message, ['message' => $exception->getMessage()]);
            return $this->errorResponse($message);
        }
        PackageLogger::add(
            $package,
            '',
            '',
            'Загружен',
            LoggerDataTypeEnum::DATA_TYPE_STRING,
            $document
        );

        $packageDocuments = PackageDocumentsService::createPackage($package);
        return $this->successResponse([
            'document' => (clone $packageDocuments)->getFilledTemplate($document),
        ]);
    }

    /**
     * Загрузка нового файла под документ
     *
     * @param Document  $document
     * @param \App\Http\Requests\Documents\DocumentUploadRequest  $request
     *
     * @return JsonResponse
     */
    public function uploadDocument(Document $document, DocumentUploadRequest $request): JsonResponse
    {
        try {
            /** @var Document $updatedDocument */
            $updatedDocument = ModelFileServiceFactory::get($document)->uploadFile($request->file);
        } catch (Throwable $exception) {
            $message = 'Ошибка обновления документа';
            Log::error($message, ['documentId' => $document->id, 'message' => $exception->getMessage()]);
            return $this->errorResponse($message);
        }

        PackageLogger::add(
            $updatedDocument->package,
            '',
            '',
            'Заменён',
            LoggerDataTypeEnum::DATA_TYPE_STRING,
            $updatedDocument
        );

        return $this->successResponse([
            'name' => $updatedDocument->name,
            'agent_sig_date' => optional($updatedDocument->agentSignature())->created_at,
            'bank_sig_date' => optional($updatedDocument->bankSignature())->created_at,
            'signatures' => $updatedDocument->signatures(),
        ]);
    }

    /**
     * @param \App\Models\Document  $document
     * @param \App\Http\Requests\Documents\DocumentDownloadRequest  $request
     *
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     * @throws \App\Exceptions\FileNotExistsException
     * @throws \App\Services\ModelFileService\Exceptions\WrongFileServiceModelException
     */
    public function download(Document $document, DocumentDownloadRequest $request): BinaryFileResponse
    {
        if ($request->isSigned) {
            return ModelFileServiceFactory::get($document)->downloadSimpleSig();
        }
        return ModelFileServiceFactory::get($document)->download();
    }

    /**
     * Удаление документа и подписей и сертификата, если он больше не используется
     *
     * @param Document  $document
     * @param DocumentRepository  $documentRepository
     *
     * @return JsonResponse
     * @throws \Exception
     */
    public function delete(Document $document, DocumentRepository $documentRepository): JsonResponse
    {
        try {
            $package = $document->package;
            $slot = $document->slot;
            if ($documentRepository->delete($document)) {
                $packageDocuments = PackageDocumentsService::createPackage($package);

                $packDocument = (clone $packageDocuments)->getDocumentBySlot($slot);
                return $this->successResponse([
                    'document' => $packDocument,
                ]);
            }
        } catch (ForbiddenPackageStatusException $exception) {
            return $this->errorResponse($exception->getMessage());
        }

        return $this->errorResponse('Ошибка удаления файла');
    }

    /**
     * @param UpdateDocumentRequest  $request
     * @param Document  $document
     * @param DocumentRepository  $documentRepository
     *
     * @return JsonResponse
     */
    public function update(
        UpdateDocumentRequest $request,
        Document $document,
        DocumentRepository $documentRepository
    ): JsonResponse {
        $doc = $documentRepository->update($document, $request->validated());
        $changes = $doc->getChanges();
        return $this->successResponse($changes);
    }

    /**
     * @param \App\Models\Document  $document
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Throwable
     */
    public function sign(Document $document): JsonResponse
    {
        try {
            $signature = Sign::signDocument($document);
            return $this->successResponse([
                'signature' => new SignatureResource($signature),
            ]);
        } catch (Throwable $exception) {
            $displayMessage = 'Ошибка подписания';
            Log::error($displayMessage, ['documentId' => $document->id, 'message' => $exception->getMessage()]);
            return $this->errorResponse($displayMessage);
        }
    }

    /**
     * @param \App\Http\Requests\Comment\CommentIndexRequest  $request
     * @param \App\Models\Document  $document
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \App\Services\Comment\Exceptions\NotAvailableExtension
     */
    public function getComments(CommentIndexRequest $request, Document $document): JsonResponse
    {
        $comments = Commentator::getComments($document, [
            'limit' => $request->limit,
            'offset' => $request->offset,
        ]);

        $currentUserId = Auth::id();

        $comments->filter(fn(Comment $comment) => $comment->user_id !== $currentUserId)
            ->each(fn(Comment $comment) => Commentator::setViewed($comment->commentable, $comment));

        return $this->successResponse([
            'comments' => CommentResource::collection($comments),
            'unreadCommentsCount' => DocumentCommentator::getUserCommentsCountList(collect([$document]))->pop(),
        ]);
    }

    /**
     * @param \App\Http\Requests\Comment\CommentStoreRequest  $request
     * @param \App\Models\Document  $document
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \App\Services\Comment\Exceptions\NotAvailableExtension
     */
    public function addComment(CommentStoreRequest $request, Document $document): JsonResponse
    {
        $comment = Commentator::createComment($document, $request->validated());
        if (!$comment) {
            return $this->errorResponse('Ошибка добавления комментария');
        }

        return $this->successResponse([
            'comment' => new CommentResource($comment),
            'unreadCommentsCount' => DocumentCommentator::getUserCommentsCountList(collect([$document]))->pop(),
        ]);
    }
}

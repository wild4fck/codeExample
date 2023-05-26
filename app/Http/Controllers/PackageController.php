<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Exceptions\NoPackageTypeException;
use App\Helpers\CollectionToResponseHelper;
use App\Http\Requests\Packages\ChangePackageStatusRequest;
use App\Http\Requests\Packages\PackageCreateRequest;
use App\Http\Requests\Packages\PackageGetTypeTemplateRequest;
use App\Http\Requests\Packages\PackagesIndexRequest;
use App\Http\Requests\Packages\PackagesListFilterRequest;
use App\Http\Resources\Packages\PackageWithDocsResource;
use App\Http\Resources\Packages\PackagesResource;
use App\Models\Agent;
use App\Models\Package;
use App\Repository\PackageRepository;
use App\Services\DocumentsService\PackageOfDocuments\PackageDocumentsService;
use App\Services\Package\PackageService;
use App\Services\PackageAvailableStatus\PackageAvailableStatusService;
use App\Services\PackageChangeStatus\ChangePackageStatusService;
use App\Services\PackageChangeStatus\Exceptions\ChangePackageStatusException;
use Auth;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * @author Wild4fck <wild4fck@yandex.ru>
 */
class PackageController extends Controller
{
    /**
     * @param \App\Http\Requests\Packages\PackagesIndexRequest  $request
     * @param \App\Repository\PackageRepository  $packageRepository
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(PackagesIndexRequest $request, PackageRepository $packageRepository): JsonResponse
    {
        return $this->successResponse(
            CollectionToResponseHelper::buildShortResponse(
                PackagesResource::collection($packageRepository->allPaginate($request->validated())),
                $request,
            )
        );
    }

    /**
     * Получение количества списка документов
     *
     * @param PackagesListFilterRequest  $request
     * @param PackageRepository  $packageRepository
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function packagesCountByFilter(
        PackagesListFilterRequest $request,
        PackageRepository $packageRepository
    ): JsonResponse {
        return $this->successResponse($packageRepository->allCounter($request->validated()));
    }

    /**
     * Получение списка документов для загрузки
     *
     * @param \App\Http\Requests\Packages\PackageGetTypeTemplateRequest  $request
     *
     * @return JsonResponse
     * @throws NoPackageTypeException
     * @throws \JsonException
     */
    public function getPackageTemplate(PackageGetTypeTemplateRequest $request): JsonResponse
    {
        return $this->successResponse([
            'templates' => PackageDocumentsService::getPackageTemplate(type: $request->type)->toArray(),
        ]);
    }

    /**
     * @param PackageCreateRequest  $request
     *
     * @return JsonResponse
     */
    public function store(PackageCreateRequest $request): JsonResponse
    {
        $package = (new PackageService())
            ->createPackage(Agent::find($request->agent_id), $request->type, $request->package_date);
        return $this->successResponse([
            'id' => $package->id,
        ]);
    }

    /**
     * @param \App\Models\Package  $package
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getPackage(Package $package): JsonResponse
    {
        return $this->successResponse(new PackageWithDocsResource($package));
    }

    /**
     * @param \App\Models\Package  $package
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \App\Exceptions\NoPackageTypeException
     */
    public function getAvailableStatuses(Package $package): JsonResponse
    {
        return $this->successResponse([
            'statuses' => PackageAvailableStatusService::getAvailableStatuses($package, Auth::user()),
        ]);
    }

    /**
     * Смена статуса пакета документов
     *
     * @param \App\Models\Package  $package
     * @param \App\Http\Requests\Packages\ChangePackageStatusRequest  $request
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Throwable
     */
    public function changeStatus(Package $package, ChangePackageStatusRequest $request): JsonResponse
    {
        $changeStatusService = new ChangePackageStatusService($package, $request->status);
        try {
            DB::beginTransaction();

            $newStatus = $changeStatusService->changeStatus();

            DB::commit();
            
            return $this->successResponse([
                'status' => $newStatus,
            ]);
        } catch (Exception | ChangePackageStatusException $exception) {
            DB::rollBack();
            Log::error('Ошибка изменения статуса пакета', ['exception' => $exception]);

            if ($exception instanceof ChangePackageStatusException) {
                return $this->errorResponse($exception->getMessage());
            }
            return $this->errorResponse('Ошибка изменения статуса пакета');
        }
    }

    /**
     * @param \App\Models\Package  $package
     *
     * @return \Illuminate\Http\JsonResponse|\Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function downloadPackage(Package $package): BinaryFileResponse|JsonResponse
    {
        try {
            return (new PackageService())->downloadPackage($package);
        } catch (\Throwable $exception) {
            $displayMessage = 'Ошибка скачивания пакета документов';
            Log::error($displayMessage, ['packageId' => $package->id, 'message' => $exception->getMessage()]);
            return $this->errorResponse($displayMessage);
        }
    }
}

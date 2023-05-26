<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Exceptions\FileNotExistsException;
use App\Helpers\ResponseHelper;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Throwable;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;
    
    /**
     * Execute an action on the controller.
     *
     * @param string  $method
     * @param array  $parameters
     *
     * @noinspection PhpMissingReturnTypeInspection
     * @throws \Illuminate\Validation\ValidationException
     */
    public function callAction($method, $parameters)
    {
        try {
            return parent::callAction($method, $parameters);
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (FileNotExistsException $exception) {
            return $this->makeResponse(
                status: 404,
                message: $exception->getMessage(),
                errors: [
                    'name' => $exception->getFilename(),
                    'path' => $exception->getPath(),
                ]
            );
        } catch (Throwable $exception) {
            Log::error($exception->getMessage(), [
                'exception' => $exception,
            ]);
            return $this->errorResponse('Возникла внутренняя ошибка!');
        }
    }
    
    /**
     * JSON ответ для фронта
     *
     * @param int  $status  Статус ответа
     * @param null|mixed  $data  Данные полученные в результате обращения
     * @param null|string  $message  Сообщение о результате обращения
     * @param array|null  $errors  Список ошибок, возникших во время обращения
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function makeResponse(
        int $status,
        mixed $data = null,
        ?string $message = null,
        ?array $errors = null
    ): JsonResponse {
        return ResponseHelper::makeResponse($status, $data, $message, $errors);
    }
    
    /**
     * @param string  $message
     * @param null  $errors
     *
     * @param null  $data
     * @param int  $errorCode
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function errorResponse(
        string $message,
        mixed $errors = null,
        mixed $data = null,
        int $errorCode = 500
    ): JsonResponse {
        return $this->makeResponse($errorCode, $data, $message, $errors);
    }
    
    /**
     * @param string  $message
     * @param mixed  $data
     * @param mixed  $errors
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function failureResponse(string $message, mixed $errors = null, mixed $data = null): JsonResponse
    {
        return $this->makeResponse(400, $data, $message, $errors);
    }
    
    /**
     * @param mixed  $data
     * @param null|string  $message
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function successResponse(mixed $data = null, ?string $message = null): JsonResponse
    {
        return $this->makeResponse(200, $data, $message);
    }
}

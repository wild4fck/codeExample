<?php

declare(strict_types=1);

namespace App\Helpers;

use App\Services\Monolog\Facades\Monolog;
use Illuminate\Http\JsonResponse;

class ResponseHelper
{
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
    public static function makeResponse(
        int $status,
        mixed $data = null,
        ?string $message = null,
        ?array $errors = null
    ): JsonResponse {
        $response = [];

        if (isset($message)) {
            $response['message'] = $message;
        }

        if (!empty($data)) {
            $response['data'] = $data;
        }

        if (!empty($errors)) {
            $response['errors'] = $errors;
        }

        $response['uuid'] = Monolog::getUuid();

        return response()->json(
            $response,
            $status,
            [
                'Content-Type' => 'application/json;charset=UTF-8',
                'Charset' => 'utf-8',
            ],
            JSON_UNESCAPED_UNICODE
        );
    }

    /**
     * @param string  $message
     * @param mixed  $data
     * @param mixed  $errors
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public static function failureResponse(string $message, mixed $errors = null, mixed $data = null): JsonResponse
    {
        return self::makeResponse(400, $data, $message, $errors);
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
    public static function errorResponse(
        string $message,
        mixed $errors = null,
        mixed $data = null,
        int $errorCode = 500
    ): JsonResponse {
        return self::makeResponse($errorCode, $data, $message, $errors);
    }

    /**
     * @param mixed  $data
     * @param null|string  $message
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public static function successResponse(mixed $data = null, ?string $message = null): JsonResponse
    {
        return self::makeResponse(200, $data, $message);
    }

    /**
     * @param null|string  $message
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public static function forbidden(?string $message = null): JsonResponse
    {
        $message = $message ?: __('response.forbidden');
        return self::makeResponse(403, null, $message);
    }
}

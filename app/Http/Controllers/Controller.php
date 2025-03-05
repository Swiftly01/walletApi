<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHandler;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller as BaseController;

abstract class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    public function successResponse(bool $status, string $message='', array|AnonymousResourceCollection|JsonResource $data = [], array $meta = [], array $errors = [], int $statusCode = 200, array $headers = []):JsonResponse
    {

        return ResponseHandler::successResponse(
            status: $status,
            message: $message,
            data: $data,
            meta: $meta,
            errors: $errors,
            statusCode: $statusCode,
            headers: $headers
        );

    }

    
    public function errorResponse(bool $status, string $message='', array $errors = [], int $statusCode = 400, array $headers = []): JsonResponse
    {
        return ResponseHandler::errorResponse(
            status: $status,
            message: $message,
            errors: $errors,
            statusCode: $statusCode,
            headers: $headers
        );
    }


    public function paginatedResponse(string $message = '', AnonymousResourceCollection $data, array $meta = [], int $statusCode = Response::HTTP_OK, array $headers = [])
    {
        return ResponseHandler::sendResponse(
            status: true,
            message: $message,
            data: $data,
            errors: [],
            meta: $meta,
            statusCode: $statusCode,
            headers:$headers
        );
    }





}

<?php

namespace App\Helpers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Response;
use Illuminate\Pagination\LengthAwarePaginator;

class ResponseHandler 
{
    public static function sendResponse(bool $status, string $message = '', array|AnonymousResourceCollection|JsonResource $data = [], array $errors = [], array $meta = [], int $statusCode, array $headers = []) :JsonResponse 
    {
          $response = [
            'status' => $status,
          ];

         if(!blank($message)) {
          $response['message'] = $message;
         }

         if(!empty($data)) {

          $response['data'] = $data['data'] ?? $data;

          if($data instanceof  AnonymousResourceCollection && $data->resource instanceof LengthAwarePaginator ) {
            $meta = array_merge($meta, self::getPaginationMeta($data->resource));
          }

         }

         if(!empty($errors)) {

          $response['errors'] = $errors['errors'] ?? $errors; 

         }

         
         $statusCode ??= $status ? Response::HTTP_OK : Response::HTTP_BAD_REQUEST;

         return response()->json(
          data: $response,
          status: $statusCode,
          headers: $headers ?? [],
         );


    }



    protected static function getPaginationMeta(LengthAwarePaginator $paginator):array
    {

      return [
        'pagination' =>[
          'total' => $paginator->total(),
          'per_page' => $paginator->perPage(),
          'current_page' => $paginator->currentPage(),
          'last_page' => $paginator->lastPage(),
          'from' => $paginator->firstItem(),
          'to' => $paginator->lastItem(),
          'next_page_url' => $paginator->nextPageUrl(),
          'prev_page_url' => $paginator->previousPageUrl(),

        ]
        ];

    }


    public static function successResponse(bool $status, string $message='', array|AnonymousResourceCollection|JsonResource $data = [], array $meta = [], array $errors = [], int $statusCode = 200, array $headers = []): JsonResponse
    {

      return static::sendResponse(
        status: $status,
        message: $message,
        data: $data,
        meta: $meta,
        errors: $errors,
        statusCode: $statusCode,
        headers: $headers,

      );

    }


    public static function errorResponse(bool $status, string $message='', array $errors = [], array $meta = [], int $statusCode = 400, array $headers = []): JsonResponse
    {

      return static::sendResponse(
        status: $status,
        message: $message,
        errors: $errors,
        data: [],
        meta: $meta,
        statusCode: $statusCode,
        headers: $headers,

      );

    }







}
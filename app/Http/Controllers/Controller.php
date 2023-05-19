<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    protected function withPagination($data = null, $message = null, $code = 200)
    {
        return response()->json(array_merge([
            'status' => $code,
            'message' => __('api.success'),
        ], $this->toCamelCase($data)), $code);
    }

    protected function success($data = null, $message = null, $code = 200)
    {
        return response()->json([
            'status' => $code,
            'message' => __('api.success'),
            'data' => $data
        ], $code);
    }

    protected function error($error = null, $code = 500)
    {
        return response()->json([
            'status' => $code,
            'message' => __('api.error'),
            'error' => $error,
            'data' => null
        ], $code);
    }

    private function toCamelCase($data): array
    {
        return collect($data)->transform(function ($item) {
            if (is_array($item)) {
                $this->toCamelCase($item);
            }

            $item = collect($item)->mapWithKeys(fn ($i, $key) => [str($key)->camel()->value() => $i]);

            return $item;
        })->toArray();
    }
}

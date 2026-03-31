<?php

namespace App\Http\Controllers;

use Throwable;
use Illuminate\Support\Facades\Log;

abstract class Controller
{
    protected function jsonServerError(string $message, Throwable $exception, int $status = 500, array $context = [])
    {
        Log::error($message, array_merge($context, [
            'exception' => get_class($exception),
            'exception_message' => $exception->getMessage(),
        ]));

        return response()->json([
            'success' => false,
            'message' => $message,
        ], $status);
    }
}

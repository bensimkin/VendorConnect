<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Spatie\Permission\Exceptions\UnauthorizedException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of exception types with their corresponding custom log levels.
     *
     * @var array<class-string<\Throwable>, \Psr\Log\LogLevel::*>
     */
    protected $levels = [
        //
    ];

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        $this->reportable(function (Throwable $e) {
            \Log::error('=== UNHANDLED EXCEPTION ===');
            \Log::error('Exception class: ' . get_class($e));
            \Log::error('Exception message: ' . $e->getMessage());
            \Log::error('Exception file: ' . $e->getFile() . ':' . $e->getLine());
            \Log::error('Request URL: ' . request()->fullUrl());
            \Log::error('Request method: ' . request()->method());
            \Log::error('Request headers: ' . json_encode(request()->headers->all()));
            \Log::error('Auth user: ' . json_encode(auth()->user()));
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            \Log::error('=== UNHANDLED EXCEPTION END ===');
        });
    }

    
}

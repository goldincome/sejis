<?php

use Illuminate\Foundation\Application;
use App\Http\Middleware\AdminMiddleware;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
//use Throwable;

// Helper function for API exception handling
if (!function_exists('handleApiException')) {
    function handleApiException(Request $request, \Throwable $e)
    {
        $status = 500;
        $message = 'Internal Server Error';
        
        if ($e instanceof ValidationException) {
            return response()->json([
                'error' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        }
        
        if ($e instanceof ModelNotFoundException) {
            $status = 404;
            $message = 'Resource not found';
        }
        
        return response()->json([
            'error' => $message,
            'message' => config('app.debug') ? $e->getMessage() : $message
        ], $status);
    }
}

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
         $middleware->alias([ 
            'admin' => AdminMiddleware::class,
        ]);
        $middleware->validateCsrfTokens(except: [
            'payment/take/success' // <-- exclude this route
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Enhanced exception reporting with structured logging
        $exceptions->reportable(function (Throwable $e) {
            Log::channel('application')->error('Application Error', [
                'exception' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'url' => request()->fullUrl(),
                'user_id' => auth()->id(),
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'timestamp' => now()->toISOString(),
            ]);
        });
        
        // Custom rendering for different exception types
        $exceptions->render(function (Throwable $e, Request $request) {
            // API error responses
            if ($request->expectsJson()) {
                return handleApiException($request, $e);
            }
            
            // Payment gateway specific errors
            if (class_basename($e) === 'PaymentException') {
                return redirect()->route('user.checkout.index')
                    ->with('error', 'Payment failed: ' . $e->getMessage());
            }
            
            // Cart errors
            if (class_basename($e) === 'CartException') {
                return redirect()->route('cart.index')
                    ->with('error', $e->getMessage());
            }
            
            // Handle 404 errors gracefully
            if ($e instanceof ModelNotFoundException) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'error' => 'Resource not found',
                        'message' => 'The requested resource could not be found.'
                    ], 404);
                }
                
                return response()->view('errors.404', [], 404);
            }
            
            return null; // Let Laravel handle other exceptions
        });
    })->create();

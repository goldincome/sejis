<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Throwable;

abstract class Controller
{
    /**
     * Handle exceptions with standardized logging and user-friendly messages
     */
    protected function handleException(Throwable $e, string $defaultMessage = 'An error occurred'): RedirectResponse
    {
        Log::error('Controller Exception: ' . $e->getMessage(), [
            'exception' => $e,
            'controller' => static::class,
            'method' => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)[1]['function'] ?? 'unknown',
            'user_id' => auth()->id(),
            'url' => request()->fullUrl(),
            'ip' => request()->ip(),
        ]);
        
        $message = config('app.debug') ? $e->getMessage() : $defaultMessage;
        
        return back()->with('error', $message)->withInput();
    }
    
    /**
     * Return a standardized success response
     */
    protected function successResponse(string $message, ?string $route = null, array $data = []): RedirectResponse
    {
        $redirect = $route ? redirect()->route($route) : back();
        
        if (!empty($data)) {
            $redirect = $redirect->with($data);
        }
        
        return $redirect->with('success', $message);
    }
    
    /**
     * Return a standardized error response
     */
    protected function errorResponse(string $message, ?string $route = null, array $data = []): RedirectResponse
    {
        $redirect = $route ? redirect()->route($route) : back();
        
        if (!empty($data)) {
            $redirect = $redirect->with($data);
        }
        
        return $redirect->with('error', $message)->withInput();
    }
    
    /**
     * Return a JSON success response for API endpoints
     */
    protected function jsonSuccess(string $message = 'Success', array $data = [], int $status = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data
        ], $status);
    }
    
    /**
     * Return a JSON error response for API endpoints
     */
    protected function jsonError(string $message = 'An error occurred', array $errors = [], int $status = 400): JsonResponse
    {
        $response = [
            'success' => false,
            'message' => $message
        ];
        
        if (!empty($errors)) {
            $response['errors'] = $errors;
        }
        
        return response()->json($response, $status);
    }
    
    /**
     * Return appropriate response based on request type (web or API)
     */
    protected function responseBasedOnRequest(
        string $successMessage,
        string $errorMessage = null,
        array $data = [],
        ?string $redirectRoute = null,
        bool $isSuccess = true
    ) {
        if (request()->expectsJson()) {
            return $isSuccess 
                ? $this->jsonSuccess($successMessage, $data)
                : $this->jsonError($errorMessage ?? $successMessage, $data);
        }
        
        return $isSuccess
            ? $this->successResponse($successMessage, $redirectRoute, $data)
            : $this->errorResponse($errorMessage ?? $successMessage, $redirectRoute, $data);
    }
    
    /**
     * Log user action for audit trail
     */
    protected function logUserAction(string $action, array $context = []): void
    {
        Log::info('User Action: ' . $action, array_merge([
            'user_id' => auth()->id(),
            'controller' => static::class,
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'url' => request()->fullUrl(),
        ], $context));
    }
    
    /**
     * Validate that user has permission for action
     */
    protected function checkPermission(string $permission, $resource = null): bool
    {
        $user = auth()->user();
        
        if (!$user) {
            return false;
        }
        
        // Basic admin check - extend this with more sophisticated permission system
        if (method_exists($user, 'hasPermission')) {
            return $user->hasPermission($permission, $resource);
        }
        
        // Fallback to user type check
        if (method_exists($user, 'isAdmin')) {
            return $user->isAdmin();
        }
        
        return false;
    }
    
    /**
     * Handle unauthorized access
     */
    protected function unauthorizedResponse(string $message = 'Unauthorized access'): RedirectResponse|JsonResponse
    {
        $this->logUserAction('unauthorized_access_attempt', [
            'attempted_action' => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)[1]['function'] ?? 'unknown'
        ]);
        
        if (request()->expectsJson()) {
            return $this->jsonError($message, [], 403);
        }
        
        return redirect()->route('home')->with('error', $message);
    }
}

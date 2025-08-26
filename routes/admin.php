<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\Auth\NewPasswordController;
use App\Http\Controllers\Admin\Auth\PasswordResetLinkController;
use App\Http\Controllers\Admin\Auth\AdminAuthenticatedSessionController;

Route::prefix('admin')->middleware('guest')->name('admin.')->group(function () {
        Route::get('login', [AdminAuthenticatedSessionController::class, 'create'])
        ->name('login');

    Route::post('login', [AdminAuthenticatedSessionController::class, 'store']);

    Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])
        ->name('password.request');

    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])
        ->name('password.email');

    Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])
        ->name('password.reset');

    Route::post('reset-password', [NewPasswordController::class, 'store'])
        ->name('password.store');
    
});
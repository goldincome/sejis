<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Front\CartController;
use App\Http\Controllers\Front\HomeController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Front\CheckoutController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\OpeningDaysController;
use App\Http\Controllers\Front\KitchenRentalController;
use App\Http\Controllers\Front\UserDashboardController;
use App\Http\Controllers\Admin\HolidayScheduleController;
use App\Http\Controllers\Admin\Auth\AdminAuthenticatedSessionController;

Route::middleware('web')->group(function () {
    Route::get('/', [HomeController::class, 'index'])->name('home');
    Route::resource('kitchen-rentals', KitchenRentalController::class);
    Route::get('/schedule/slots', [HomeController::class, 'getTimeSlots'])->name('time.slots');
    // Cart Routes
    Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
    Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');
    Route::post('/cart/update', [CartController::class, 'update'])->name('cart.update');
    Route::post('/cart/remove', [CartController::class, 'remove'])->name('cart.remove');
    Route::post('/cart/clear', [CartController::class, 'clear'])->name('cart.clear');
        Route::post('/payment/take/success', [CheckoutController::class, 'takepaymentSuccess'])->name('takepayment.success');
});

//Customer route
Route::prefix('user')->name('user.')->middleware('auth')->group(function () {
    Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
    Route::post('/checkout', [CheckoutController::class, 'store'])->name('process.payment');
    Route::get('/payment/stp/success', [CheckoutController::class, 'stripeSuccess'])->name('stripe.success');
    Route::get('/payment/pp/success', [CheckoutController::class, 'paypalSuccess'])->name('paypal.success');
    Route::get('/payment/cancelled', [CheckoutController::class, 'paymentCancelled'])->name('payment.cancelled');
    Route::get('/checkout/success/{order}', [CheckoutController::class, 'checkoutSuccess'])->name('checkout.success');
    Route::get('/dashboard', [UserDashboardController::class, 'index'])->name('dashboard');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    // PayPal Callbacks
    Route::get('/paypal/return', [PaymentController::class, 'handlePayPalReturn'])
        ->name('payment.paypal.return');
    
    Route::get('/paypal/cancel', [PaymentController::class, 'handlePaymentCancel'])
        ->name('payment.paypal.cancel');
    
    // TakePayments Callbacks
    Route::post('/takepayments/return', [PaymentController::class, 'handleTakePaymentsReturn'])
        ->name('payment.takepayments.return');
    
    Route::get('/takepayments/cancel', [PaymentController::class, 'handlePaymentCancel'])
        ->name('payment.takepayments.cancel');
});

//Admin route

Route::prefix('admin')->name('admin.')->middleware(['auth','admin'])->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard.index');
    Route::post('logout', [AdminAuthenticatedSessionController::class, 'destroy'])
        ->name('logout');
    Route::resource('/categories', CategoryController::class)->except('show');
    Route::resource('products', ProductController::class);
    Route::resource('admins', AdminController::class);
    Route::resource('customers', CustomerController::class);
    Route::resource('off-dates', HolidayScheduleController::class);
    Route::resource('opening-days', OpeningDaysController::class)->only(['index','store']);
    Route::resource('orders', OrderController::class)->only(['index','update','edit','show']);
});


require __DIR__.'/auth.php';
require __DIR__.'/admin.php';

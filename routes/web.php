<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Front\ProfileController;
use App\Http\Controllers\Front\CartController;
use App\Http\Controllers\Front\HomeController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\PaymentMonitoringController;
use App\Http\Controllers\Webhooks\StripeWebhookController;
use App\Http\Controllers\Webhooks\PayPalWebhookController;
use App\Http\Controllers\Webhooks\TakePaymentsWebhookController;
use App\Http\Controllers\Webhooks\BankDepositWebhookController;
use App\Http\Controllers\Front\CheckoutController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\OpeningDaysController;
use App\Http\Controllers\Front\KitchenRentalController;
use App\Http\Controllers\Front\EquipmentRentalController;
use App\Http\Controllers\Front\UserDashboardController;
use App\Http\Controllers\Admin\HolidayScheduleController;
use App\Http\Controllers\Admin\Auth\AdminAuthenticatedSessionController;
use App\Http\Controllers\Front\SearchController;
use App\Http\Controllers\Admin\EmailPreviewController;
use App\Http\Controllers\Front\PageController;

Route::middleware('web')->group(function () {
    Route::get('/', [HomeController::class, 'index'])->name('home');
    Route::get('about-us', [PageController::class, 'aboutUs'])->name('about-us');
    Route::get('contact-us', [PageController::class, 'contactUs'])->name('contact-us');
    Route::post('contact-us', [PageController::class, 'processContactUs'])->name('process-contact');
    Route::get('privacy-policy', [PageController::class, 'privacyPolicy'])->name('privacy-policy');
    Route::get('terms-of-service', [PageController::class, 'termsOfService'])->name('terms-of-service');
    Route::get('booking-policy', [PageController::class, 'bookingPolicy'])->name('booking-policy');
    
    // Search Routes
    Route::get('/search', [SearchController::class, 'index'])->name('search.index');
    Route::get('/search/ajax', [SearchController::class, 'ajaxSearch'])->name('search.ajax');
    Route::get('/search/suggestions', [SearchController::class, 'suggestions'])->name('search.suggestions');
    Route::get('/search/fulltext', [SearchController::class, 'fullTextSearch'])->name('search.fulltext');
    Route::get('/search/availability', [SearchController::class, 'searchByAvailability'])->name('search.availability');
    Route::get('/search/popular', [SearchController::class, 'popularTerms'])->name('search.popular');
    Route::get('/search/trending', [SearchController::class, 'trending'])->name('search.trending');
    Route::get('/search/filter-options', [SearchController::class, 'filterOptions'])->name('search.filter-options');
    Route::get('/search/analytics', [SearchController::class, 'analytics'])->name('search.analytics');
    Route::post('/search/clear-cache', [SearchController::class, 'clearCache'])->name('search.clear-cache');
    Route::resource('kitchen-rentals', KitchenRentalController::class);
    Route::resource('equipment-rentals', EquipmentRentalController::class);
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
    
    // Dashboard routes
    Route::get('/dashboard', [UserDashboardController::class, 'index'])->name('dashboard');
    Route::get('/orders', [UserDashboardController::class, 'orders'])->name('orders');
    Route::get('/orders/{orderReference}', [UserDashboardController::class, 'orderDetails'])->name('order.details');
    Route::get('/orders/{orderReference}/invoice', [UserDashboardController::class, 'downloadInvoice'])->name('order.invoice');
    Route::post('/orders/{orderReference}/cancel', [UserDashboardController::class, 'cancelOrder'])->name('order.cancel');
    Route::post('/orders/{orderReference}/refund', [UserDashboardController::class, 'requestRefund'])->name('order.refund');
    
    // Notifications and user info
    Route::get('/notifications', [UserDashboardController::class, 'notifications'])->name('notifications');
    Route::post('/notifications/{notificationId}/read', [UserDashboardController::class, 'markNotificationAsRead'])->name('notifications.read');
    Route::get('/profile-completion', [UserDashboardController::class, 'getProfileCompletion'])->name('profile.completion');
    Route::get('/loyalty-info', [UserDashboardController::class, 'loyaltyInfo'])->name('loyalty.info');
    
    // Profile management
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/change-password', [ProfileController::class, 'changePassword'])->name('change.password');
    
    // PayPal Callbacks - TODO: Implement PaymentController
    // Route::get('/paypal/return', [PaymentController::class, 'handlePayPalReturn'])
    //     ->name('payment.paypal.return');
    
    // Route::get('/paypal/cancel', [PaymentController::class, 'handlePaymentCancel'])
    //     ->name('payment.paypal.cancel');
    
    // TakePayments Callbacks - TODO: Implement PaymentController
    // Route::post('/takepayments/return', [PaymentController::class, 'handleTakePaymentsReturn'])
    //     ->name('payment.takepayments.return');
    
    // Route::get('/takepayments/cancel', [PaymentController::class, 'handlePaymentCancel'])
    //     ->name('payment.takepayments.cancel');
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
    Route::resource('orders', OrderController::class)->only(['index','update','edit','show','destroy']);
    
    // Order inventory management routes
    Route::prefix('orders/{order}')->name('orders.')->group(function () {
        Route::post('/reserve-stock', [OrderController::class, 'reserveStock'])->name('reserve-stock');
        Route::post('/release-stock', [OrderController::class, 'releaseStock'])->name('release-stock');
        Route::post('/process-dispatch', [OrderController::class, 'processDispatch'])->name('process-dispatch');
        Route::post('/process-return', [OrderController::class, 'processReturn'])->name('process-return');
        Route::get('/check-availability', [OrderController::class, 'checkInventoryAvailability'])->name('check-availability');
        Route::get('/inventory-status', [OrderController::class, 'getInventoryStatus'])->name('inventory-status');
    });
    
    // Settings routes
    Route::resource('settings', SettingController::class);
    Route::post('settings/quick-update', [SettingController::class, 'quickUpdate'])->name('settings.quick-update');
    Route::post('settings/clear-cache', [SettingController::class, 'clearCache'])->name('settings.clear-cache');
    Route::get('settings/export', [SettingController::class, 'export'])->name('settings.export');
    
    // Payment Monitoring routes
    Route::prefix('payment-monitoring')->name('payment-monitoring.')->group(function () {
        Route::get('/', [PaymentMonitoringController::class, 'index'])->name('index');
        Route::get('/audit-logs', [PaymentMonitoringController::class, 'auditLogs'])->name('audit-logs');
        Route::get('/webhook-logs', [PaymentMonitoringController::class, 'webhookLogs'])->name('webhook-logs');
        Route::get('/realtime-stats', [PaymentMonitoringController::class, 'realtimeStats'])->name('realtime-stats');
        Route::get('/payment-trends', [PaymentMonitoringController::class, 'paymentTrends'])->name('payment-trends');
        Route::get('/export', [PaymentMonitoringController::class, 'exportData'])->name('export');
    });
    
    // Email Notification Management routes
    Route::prefix('email-notifications')->name('email-notifications.')->group(function () {
        Route::get('/', [EmailPreviewController::class, 'index'])->name('index');
        Route::get('/preview/{type}', [EmailPreviewController::class, 'preview'])->name('preview');
        Route::post('/send-test', [EmailPreviewController::class, 'sendTestEmail'])->name('send-test');
        Route::get('/configuration', [EmailPreviewController::class, 'showConfiguration'])->name('configuration');
        Route::post('/test-configuration', [EmailPreviewController::class, 'testConfiguration'])->name('test-configuration');
        Route::get('/settings', [EmailPreviewController::class, 'settings'])->name('settings');
        Route::post('/settings', [EmailPreviewController::class, 'updateSettings'])->name('settings.update');
    });
    
    // Inventory Management routes
    Route::prefix('inventory')->name('inventory.')->group(function () {
        Route::get('/', [App\Http\Controllers\Admin\InventoryController::class, 'index'])->name('index');
        Route::get('/items', [App\Http\Controllers\Admin\InventoryController::class, 'items'])->name('items');
        Route::get('/items/create', [App\Http\Controllers\Admin\InventoryController::class, 'create'])->name('create');
        Route::post('/items', [App\Http\Controllers\Admin\InventoryController::class, 'store'])->name('store');
        Route::get('/items/{inventoryItem}', [App\Http\Controllers\Admin\InventoryController::class, 'show'])->name('show');
        Route::get('/items/{inventoryItem}/edit', [App\Http\Controllers\Admin\InventoryController::class, 'edit'])->name('edit');
        Route::put('/items/{inventoryItem}', [App\Http\Controllers\Admin\InventoryController::class, 'update'])->name('update');
        Route::delete('/items/{inventoryItem}', [App\Http\Controllers\Admin\InventoryController::class, 'destroy'])->name('destroy');
        
        // Stock management
        Route::post('/items/{inventoryItem}/adjust-stock', [App\Http\Controllers\Admin\InventoryController::class, 'adjustStock'])->name('adjust-stock');
        Route::post('/transfer', [App\Http\Controllers\Admin\InventoryController::class, 'transfer'])->name('transfer');
        Route::post('/schedule-maintenance', [App\Http\Controllers\Admin\InventoryController::class, 'scheduleMaintenance'])->name('schedule-maintenance');
        Route::post('/complete-maintenance', [App\Http\Controllers\Admin\InventoryController::class, 'completeMaintenance'])->name('complete-maintenance');
        
        // Movement tracking
        Route::get('/movements', [App\Http\Controllers\Admin\InventoryController::class, 'movements'])->name('movements');
        Route::post('/manual-movement', [App\Http\Controllers\Admin\InventoryController::class, 'manualMovement'])->name('manual-movement');
        Route::get('/movements/{movement}/details', [App\Http\Controllers\Admin\InventoryController::class, 'movementDetails'])->name('movement-details');
        Route::post('/movements/{movement}/reverse', [App\Http\Controllers\Admin\InventoryController::class, 'reverseMovement'])->name('reverse-movement');
        
        // Alerts and reports
        Route::get('/alerts', [App\Http\Controllers\Admin\InventoryController::class, 'alerts'])->name('alerts');
        Route::post('/send-low-stock-alerts', [App\Http\Controllers\Admin\InventoryController::class, 'sendLowStockAlerts'])->name('send-low-stock-alerts');
        Route::get('/statistics', [App\Http\Controllers\Admin\InventoryController::class, 'statistics'])->name('statistics');
        
        // Export and bulk operations
        Route::get('/export', [App\Http\Controllers\Admin\InventoryController::class, 'export'])->name('export');
        Route::get('/export-movements', [App\Http\Controllers\Admin\InventoryController::class, 'exportMovements'])->name('export-movements');
        Route::get('/low-stock-report', [App\Http\Controllers\Admin\InventoryController::class, 'lowStockReport'])->name('low-stock-report');
        Route::post('/bulk-update', [App\Http\Controllers\Admin\InventoryController::class, 'bulkUpdate'])->name('bulk-update');
        
        // Availability checking
        Route::post('/check-availability', [App\Http\Controllers\Admin\InventoryController::class, 'checkAvailability'])->name('check-availability');
        
        // Inventory Reporting routes
        Route::prefix('reports')->name('reports.')->group(function () {
            Route::get('/', [App\Http\Controllers\Admin\InventoryReportController::class, 'index'])->name('index');
            Route::get('/stock-valuation', [App\Http\Controllers\Admin\InventoryReportController::class, 'stockValuation'])->name('stock-valuation');
            Route::get('/movement-analytics', [App\Http\Controllers\Admin\InventoryReportController::class, 'movementAnalytics'])->name('movement-analytics');
            Route::get('/stock-turnover', [App\Http\Controllers\Admin\InventoryReportController::class, 'stockTurnover'])->name('stock-turnover');
            Route::get('/abc-analysis', [App\Http\Controllers\Admin\InventoryReportController::class, 'abcAnalysis'])->name('abc-analysis');
            Route::get('/low-stock-impact', [App\Http\Controllers\Admin\InventoryReportController::class, 'lowStockImpact'])->name('low-stock-impact');
            Route::post('/comprehensive', [App\Http\Controllers\Admin\InventoryReportController::class, 'comprehensiveReport'])->name('comprehensive');
            Route::get('/dashboard-widgets', [App\Http\Controllers\Admin\InventoryReportController::class, 'dashboardWidgets'])->name('dashboard-widgets');
            Route::post('/schedule', [App\Http\Controllers\Admin\InventoryReportController::class, 'scheduleReports'])->name('schedule');
        });
    });
});


require __DIR__.'/auth.php';
require __DIR__.'/admin.php';

// Webhook Routes (outside of middleware groups for external access)
Route::post('/webhooks/stripe', [StripeWebhookController::class, 'handle'])->name('webhooks.stripe');
Route::post('/webhooks/paypal', [PayPalWebhookController::class, 'handle'])->name('webhooks.paypal');
Route::post('/webhooks/takepayments', [TakePaymentsWebhookController::class, 'handle'])->name('webhooks.takepayments');
Route::post('/webhooks/bank-deposit', [BankDepositWebhookController::class, 'handle'])->name('webhooks.bank-deposit');
Route::post('/admin/bank-deposit/manual-verification', [BankDepositWebhookController::class, 'manualVerification'])
    ->name('admin.bank-deposit.manual-verification')
    ->middleware(['auth', 'admin']);

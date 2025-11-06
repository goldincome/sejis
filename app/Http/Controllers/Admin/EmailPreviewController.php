<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\User;
use App\Mail\OrderConfirmationMail;
use App\Mail\OrderStatusUpdateMail;
use App\Mail\AdminOrderNotificationMail;
use App\Mail\WelcomeEmailMail;
use App\Services\NotificationService;
use App\Services\SettingsService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;
use Exception;

class EmailPreviewController extends Controller
{
    protected NotificationService $notificationService;
    protected SettingsService $settingsService;

    public function __construct(NotificationService $notificationService, SettingsService $settingsService)
    {
        $this->notificationService = $notificationService;
        $this->settingsService = $settingsService;
    }

    /**
     * Display email preview dashboard
     */
    public function index()
    {
        // Get email statistics for display
        $stats = [
            'order_confirmations' => Order::whereMonth('created_at', now()->month)->count(),
            'status_updates' => Order::whereMonth('updated_at', now()->month)
                                    ->where('created_at', '!=', 'updated_at')
                                    ->count(),
            'welcome_emails' => User::whereMonth('created_at', now()->month)->count(),
            'failed_emails' => 0 // This would come from failed jobs table
        ];
        
        return view('admin.email-notifications.index', compact('stats'));
    }

    /**
     * Preview specific email type
     */
    public function preview($type, Request $request)
    {
        switch ($type) {
            case 'order_confirmation':
                return $this->previewOrderConfirmation($request);
            case 'status_update':
                return $this->previewStatusUpdate($request);
            case 'admin_notification':
                return $this->previewAdminNotification($request);
            case 'welcome_email':
                return $this->previewWelcomeEmail($request);
            default:
                abort(404, 'Email type not found');
        }
    }

    /**
     * Show email configuration page
     */
    public function showConfiguration()
    {
        $config = [
            'driver' => Config::get('mail.default'),
            'host' => Config::get('mail.mailers.smtp.host'),
            'port' => Config::get('mail.mailers.smtp.port'),
            'encryption' => Config::get('mail.mailers.smtp.encryption'),
            'username' => Config::get('mail.mailers.smtp.username'),
            'password' => Config::get('mail.mailers.smtp.password') ? '***' : null,
            'from' => [
                'address' => Config::get('mail.from.address'),
                'name' => Config::get('mail.from.name')
            ]
        ];
        
        return view('admin.email-notifications.configuration', compact('config'));
    }

    /**
     * Test email configuration
     */
    public function testConfiguration(Request $request): JsonResponse
    {
        $request->validate([
            'test_email' => 'required|email',
            'test_type' => 'required|in:connection,send_email,full_test'
        ]);
        
        try {
            $testType = $request->get('test_type');
            $testEmail = $request->get('test_email');
            $results = [];
            
            // Connection test
            if (in_array($testType, ['connection', 'full_test'])) {
                try {
                    $transport = Mail::getSwiftMailer()->getTransport();
                    if (method_exists($transport, 'start')) {
                        $transport->start();
                    }
                    $results['connection'] = true;
                } catch (Exception $e) {
                    $results['connection'] = false;
                    $results['connection_error'] = $e->getMessage();
                }
            }
            
            // Authentication test (for SMTP)
            if (in_array($testType, ['full_test']) && Config::get('mail.default') === 'smtp') {
                $results['authentication'] = $results['connection'] ?? false;
            }
            
            // Send test email
            if (in_array($testType, ['send_email', 'full_test'])) {
                try {
                    $user = $this->getSampleUser();
                    if ($user) {
                        Mail::to($testEmail)->send(new WelcomeEmailMail($user, false));
                        $results['email_sent'] = true;
                    } else {
                        throw new Exception('No sample user available');
                    }
                } catch (Exception $e) {
                    $results['email_sent'] = false;
                    $results['email_error'] = $e->getMessage();
                }
            }
            
            $overallSuccess = !in_array(false, $results, true);
            
            return response()->json([
                'success' => $overallSuccess,
                'message' => $overallSuccess ? 'Configuration test completed successfully' : 'Configuration test completed with issues',
                'results' => $results,
                'error_details' => isset($results['connection_error']) ? $results['connection_error'] : (isset($results['email_error']) ? $results['email_error'] : null)
            ]);
            
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Configuration test failed: ' . $e->getMessage(),
                'results' => [],
                'error_details' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show email settings page
     */
    public function settings()
    {
        return view('admin.email-notifications.settings');
    }

    /**
     * Update email settings
     */
    public function updateSettings(Request $request): JsonResponse
    {
        try {
            $settings = $request->except(['_token', '_method']);
            
            // Save each setting
            foreach ($settings as $key => $value) {
                // Handle checkbox values
                if (in_array($key, [
                    'order_confirmation_enabled',
                    'order_status_updates_enabled', 
                    'welcome_emails_enabled',
                    'admin_new_order_alerts',
                    'admin_payment_alerts',
                    'admin_high_value_alerts',
                    'welcome_discount_enabled',
                    'email_queue_enabled'
                ])) {
                    $value = $value === 'on' ? true : false;
                }
                
                $this->settingsService->set($key, $value);
            }
            
            Log::info('Email settings updated', [
                'admin_user' => auth()->id(),
                'settings_count' => count($settings)
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Email settings updated successfully'
            ]);
            
        } catch (Exception $e) {
            Log::error('Failed to update email settings', [
                'error' => $e->getMessage(),
                'admin_user' => auth()->id()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to update settings: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Preview order confirmation email
     */
    public function previewOrderConfirmation(Request $request)
    {
        try {
            // Get a sample order or create one for preview
            $order = $this->getSampleOrder($request->get('order_id'));
            
            if (!$order) {
                return response()->json(['error' => 'No orders available for preview'], 404);
            }
            
            $mailable = new OrderConfirmationMail($order);
            
            // Render the email content
            $content = $mailable->render();
            
            return response($content)->header('Content-Type', 'text/html');
            
        } catch (Exception $e) {
            return response()->json([
                'error' => 'Failed to preview email: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Preview order status update email
     */
    public function previewStatusUpdate(Request $request)
    {
        try {
            $order = $this->getSampleOrder($request->get('order_id'));
            
            if (!$order) {
                return response()->json(['error' => 'No orders available for preview'], 404);
            }
            
            $oldStatus = $request->get('old_status', 'pending');
            $newStatus = $request->get('new_status', 'confirmed');
            $updateNote = $request->get('update_note', 'Your order status has been updated.');
            
            $mailable = new OrderStatusUpdateMail($order, $oldStatus, $newStatus, $updateNote);
            
            $content = $mailable->render();
            
            return response($content)->header('Content-Type', 'text/html');
            
        } catch (Exception $e) {
            return response()->json([
                'error' => 'Failed to preview email: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Preview admin notification email
     */
    public function previewAdminNotification(Request $request)
    {
        try {
            $order = $this->getSampleOrder($request->get('order_id'));
            
            if (!$order) {
                return response()->json(['error' => 'No orders available for preview'], 404);
            }
            
            $notificationType = $request->get('notification_type', 'new_order');
            $additionalInfo = $request->get('additional_info', 'Sample notification for preview');
            
            $mailable = new AdminOrderNotificationMail($order, $notificationType, $additionalInfo);
            
            $content = $mailable->render();
            
            return response($content)->header('Content-Type', 'text/html');
            
        } catch (Exception $e) {
            return response()->json([
                'error' => 'Failed to preview email: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Preview welcome email
     */
    public function previewWelcomeEmail(Request $request)
    {
        try {
            $user = $this->getSampleUser($request->get('user_id'));
            
            if (!$user) {
                return response()->json(['error' => 'No users available for preview'], 404);
            }
            
            $verificationRequired = $request->boolean('verification_required', false);
            
            $mailable = new WelcomeEmailMail($user, $verificationRequired);
            
            $content = $mailable->render();
            
            return response($content)->header('Content-Type', 'text/html');
            
        } catch (Exception $e) {
            return response()->json([
                'error' => 'Failed to preview email: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Send test emails
     */
    public function sendTestEmail(Request $request): JsonResponse
    {
        $request->validate([
            'email_type' => 'required|in:order_confirmation,status_update,admin_notification,welcome_email',
            'test_email' => 'required|email',
            'order_id' => 'nullable|exists:orders,id',
            'user_id' => 'nullable|exists:users,id'
        ]);
        
        try {
            $testEmail = $request->get('test_email');
            $emailType = $request->get('email_type');
            
            switch ($emailType) {
                case 'order_confirmation':
                    $order = $this->getSampleOrder($request->get('order_id'));
                    if (!$order) {
                        return response()->json(['error' => 'No orders available for testing'], 404);
                    }
                    Mail::to($testEmail)->send(new OrderConfirmationMail($order));
                    break;
                    
                case 'status_update':
                    $order = $this->getSampleOrder($request->get('order_id'));
                    if (!$order) {
                        return response()->json(['error' => 'No orders available for testing'], 404);
                    }
                    Mail::to($testEmail)->send(new OrderStatusUpdateMail(
                        $order, 
                        'pending', 
                        'confirmed', 
                        'Test status update email'
                    ));
                    break;
                    
                case 'admin_notification':
                    $order = $this->getSampleOrder($request->get('order_id'));
                    if (!$order) {
                        return response()->json(['error' => 'No orders available for testing'], 404);
                    }
                    Mail::to($testEmail)->send(new AdminOrderNotificationMail(
                        $order, 
                        'new_order', 
                        'Test admin notification email'
                    ));
                    break;
                    
                case 'welcome_email':
                    $user = $this->getSampleUser($request->get('user_id'));
                    if (!$user) {
                        return response()->json(['error' => 'No users available for testing'], 404);
                    }
                    Mail::to($testEmail)->send(new WelcomeEmailMail($user, false));
                    break;
            }
            
            Log::info('Test email sent successfully', [
                'email_type' => $emailType,
                'test_email' => $testEmail,
                'admin_user' => auth()->id()
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Test email sent successfully to ' . $testEmail
            ]);
            
        } catch (Exception $e) {
            Log::error('Failed to send test email', [
                'email_type' => $request->get('email_type'),
                'test_email' => $request->get('test_email'),
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to send test email: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get email statistics
     */
    public function getEmailStats(Request $request): JsonResponse
    {
        try {
            $period = $request->get('period', 'today');
            $stats = $this->notificationService->getEmailStats($period);
            
            return response()->json([
                'success' => true,
                'stats' => $stats,
                'period' => $period
            ]);
            
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get email stats: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Clear email analytics cache
     */
    public function clearEmailCache(): JsonResponse
    {
        try {
            $result = $this->notificationService->clearEmailAnalytics();
            
            if ($result) {
                return response()->json([
                    'success' => true,
                    'message' => 'Email analytics cache cleared successfully'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to clear email analytics cache'
                ], 500);
            }
            
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error clearing cache: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get available orders for email testing
     */
    public function getAvailableOrders(): JsonResponse
    {
        try {
            $orders = Order::with(['user', 'orderDetails.product'])
                ->latest()
                ->limit(10)
                ->get()
                ->map(function ($order) {
                    return [
                        'id' => $order->id,
                        'reference' => $order->reference,
                        'customer_name' => $order->user->name ?? 'Unknown',
                        'total' => $order->total,
                        'status' => $order->status,
                        'created_at' => $order->created_at->format('Y-m-d H:i:s')
                    ];
                });
                
            return response()->json([
                'success' => true,
                'orders' => $orders
            ]);
            
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get orders: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get available users for email testing
     */
    public function getAvailableUsers(): JsonResponse
    {
        try {
            $users = User::latest()
                ->limit(10)
                ->get(['id', 'name', 'email', 'created_at'])
                ->map(function ($user) {
                    return [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'created_at' => $user->created_at->format('Y-m-d H:i:s')
                    ];
                });
                
            return response()->json([
                'success' => true,
                'users' => $users
            ]);
            
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get users: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get a sample order for preview/testing
     */
    private function getSampleOrder(?int $orderId = null): ?Order
    {
        if ($orderId) {
            $order = Order::with(['user', 'orderDetails.product'])->find($orderId);
            if ($order) {
                return $order;
            }
        }
        
        // Get the latest order with all necessary relationships
        return Order::with(['user', 'orderDetails.product'])
            ->whereHas('user')
            ->whereHas('orderDetails.product')
            ->latest()
            ->first();
    }

    /**
     * Get a sample user for preview/testing
     */
    private function getSampleUser(?int $userId = null): ?User
    {
        if ($userId) {
            $user = User::find($userId);
            if ($user) {
                return $user;
            }
        }
        
        // Get the latest user
        return User::latest()->first();
    }
}

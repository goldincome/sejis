<?php

namespace App\Services;

use App\Models\User;
use App\Models\Order;
use App\Mail\OrderConfirmationMail;
use App\Mail\OrderStatusUpdateMail;
use App\Mail\AdminOrderNotificationMail;
use App\Mail\WelcomeEmailMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Queue;
use Exception;

class NotificationService
{
    /**
     * Send order confirmation email to customer
     */
    public function sendOrderConfirmation(Order $order): bool
    {
        try {
            if (!$order->user || !$order->user->email) {
                Log::warning('Cannot send order confirmation: No customer email', [
                    'order_id' => $order->id,
                    'order_reference' => $order->order_reference
                ]);
                return false;
            }

            Mail::to($order->user->email)
                ->send(new OrderConfirmationMail($order));

            Log::info('Order confirmation email sent', [
                'order_id' => $order->id,
                'order_reference' => $order->order_reference,
                'customer_email' => $order->user->email
            ]);

            // Track email sending for analytics
            $this->trackEmailSent('order_confirmation', $order->user->id, $order->id);

            return true;
        } catch (Exception $e) {
            Log::error('Failed to send order confirmation email', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }

    /**
     * Send order status update email to customer
     */
    public function sendOrderStatusUpdate(Order $order, string $oldStatus, string $newStatus, ?string $updateNote = null): bool
    {
        try {
            if (!$order->user || !$order->user->email) {
                Log::warning('Cannot send status update: No customer email', [
                    'order_id' => $order->id,
                    'order_reference' => $order->order_reference
                ]);
                return false;
            }

            Mail::to($order->user->email)
                ->send(new OrderStatusUpdateMail($order, $oldStatus, $newStatus, $updateNote));

            Log::info('Order status update email sent', [
                'order_id' => $order->id,
                'order_reference' => $order->order_reference,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'customer_email' => $order->user->email
            ]);

            // Track email sending for analytics
            $this->trackEmailSent('order_status_update', $order->user->id, $order->id);

            return true;
        } catch (Exception $e) {
            Log::error('Failed to send order status update email', [
                'order_id' => $order->id,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Send admin notification email
     */
    public function sendAdminOrderNotification(Order $order, string $notificationType, ?string $additionalInfo = null): bool
    {
        try {
            $adminEmails = $this->getAdminEmails($notificationType);
            
            if (empty($adminEmails)) {
                Log::warning('No admin emails configured for notification type', [
                    'notification_type' => $notificationType,
                    'order_id' => $order->id
                ]);
                return false;
            }

            foreach ($adminEmails as $email) {
                Mail::to($email)
                    ->send(new AdminOrderNotificationMail($order, $notificationType, $additionalInfo));
            }

            Log::info('Admin notification email sent', [
                'order_id' => $order->id,
                'notification_type' => $notificationType,
                'admin_emails' => $adminEmails,
                'additional_info' => $additionalInfo
            ]);

            // Track admin notification for analytics
            $this->trackEmailSent('admin_notification', null, $order->id, $notificationType);

            return true;
        } catch (Exception $e) {
            Log::error('Failed to send admin notification email', [
                'order_id' => $order->id,
                'notification_type' => $notificationType,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Send welcome email to new customer
     */
    public function sendWelcomeEmail(User $user, bool $isVerificationRequired = false): bool
    {
        try {
            if (!$user->email) {
                Log::warning('Cannot send welcome email: No user email', [
                    'user_id' => $user->id
                ]);
                return false;
            }

            Mail::to($user->email)
                ->send(new WelcomeEmailMail($user, $isVerificationRequired));

            Log::info('Welcome email sent', [
                'user_id' => $user->id,
                'user_email' => $user->email,
                'verification_required' => $isVerificationRequired
            ]);

            // Track email sending for analytics
            $this->trackEmailSent('welcome_email', $user->id);

            return true;
        } catch (Exception $e) {
            Log::error('Failed to send welcome email', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Send multiple notifications based on order events
     */
    public function sendOrderEventNotifications(Order $order, string $event, array $options = []): array
    {
        $results = [];

        switch ($event) {
            case 'order_placed':
                // Send customer confirmation
                $results['customer_confirmation'] = $this->sendOrderConfirmation($order);
                
                // Send admin notification for new order
                $results['admin_notification'] = $this->sendAdminOrderNotification(
                    $order, 
                    'new_order',
                    $options['admin_note'] ?? null
                );

                // Send high value order alert if applicable
                if ($order->total_amount > setting('high_value_threshold', 1000)) {
                    $results['high_value_alert'] = $this->sendAdminOrderNotification(
                        $order,
                        'high_value_order',
                        "Order value: £{$order->total_amount}"
                    );
                }
                break;

            case 'payment_received':
                $results['admin_notification'] = $this->sendAdminOrderNotification(
                    $order,
                    'payment_received',
                    "Payment method: {$order->payment_method}"
                );
                break;

            case 'payment_failed':
                $results['admin_notification'] = $this->sendAdminOrderNotification(
                    $order,
                    'payment_failed',
                    $options['failure_reason'] ?? 'Payment processing failed'
                );
                break;

            case 'order_cancelled':
                $results['customer_notification'] = $this->sendOrderStatusUpdate(
                    $order,
                    $options['old_status'] ?? 'pending',
                    'cancelled',
                    $options['cancellation_reason'] ?? null
                );

                $results['admin_notification'] = $this->sendAdminOrderNotification(
                    $order,
                    'cancellation_request',
                    $options['cancellation_reason'] ?? null
                );
                break;

            case 'delivery_issue':
                $results['admin_notification'] = $this->sendAdminOrderNotification(
                    $order,
                    'delivery_issue',
                    $options['issue_description'] ?? null
                );
                break;
        }

        return $results;
    }

    /**
     * Get admin emails based on notification type
     */
    private function getAdminEmails(string $notificationType): array
    {
        $defaultAdminEmails = [
            setting('admin_email', 'admin@sejiskitchenrental.com')
        ];

        // Get specific admin emails based on notification type
        $typeSpecificEmails = match($notificationType) {
            'payment_failed', 'payment_received' => [
                setting('finance_admin_email', setting('admin_email', 'admin@sejiskitchenrental.com'))
            ],
            'high_value_order' => [
                setting('sales_manager_email', setting('admin_email', 'admin@sejiskitchenrental.com')),
                setting('finance_admin_email', setting('admin_email', 'admin@sejiskitchenrental.com'))
            ],
            'delivery_issue' => [
                setting('operations_admin_email', setting('admin_email', 'admin@sejiskitchenrental.com'))
            ],
            'customer_complaint' => [
                setting('customer_service_email', setting('admin_email', 'admin@sejiskitchenrental.com'))
            ],
            default => $defaultAdminEmails
        };

        // Remove duplicates and invalid emails
        return array_filter(array_unique($typeSpecificEmails), function($email) {
            return filter_var($email, FILTER_VALIDATE_EMAIL);
        });
    }

    /**
     * Track email sending for analytics
     */
    private function trackEmailSent(string $emailType, ?int $userId = null, ?int $orderId = null, ?string $metadata = null): void
    {
        try {
            $key = "email_stats:sent:{$emailType}:" . date('Y-m-d');
            Cache::increment($key);
            
            // Set expiration for 30 days
            Cache::put($key, Cache::get($key, 0), now()->addDays(30));

            // Track detailed analytics
            $analyticsData = [
                'email_type' => $emailType,
                'user_id' => $userId,
                'order_id' => $orderId,
                'metadata' => $metadata,
                'sent_at' => now()->toISOString(),
                'date' => date('Y-m-d'),
                'hour' => date('H')
            ];

            // Store in cache for recent analytics (last 24 hours)
            $recentKey = "email_analytics:recent:" . date('Y-m-d-H');
            $recentData = Cache::get($recentKey, []);
            $recentData[] = $analyticsData;
            Cache::put($recentKey, $recentData, now()->addHours(25));

        } catch (Exception $e) {
            // Don't fail email sending if analytics tracking fails
            Log::warning('Failed to track email analytics', [
                'email_type' => $emailType,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get email sending statistics
     */
    public function getEmailStats(string $period = 'today'): array
    {
        try {
            $stats = [];
            $emailTypes = ['order_confirmation', 'order_status_update', 'admin_notification', 'welcome_email'];

            foreach ($emailTypes as $type) {
                switch ($period) {
                    case 'today':
                        $key = "email_stats:sent:{$type}:" . date('Y-m-d');
                        $stats[$type] = Cache::get($key, 0);
                        break;
                    
                    case 'week':
                        $total = 0;
                        for ($i = 0; $i < 7; $i++) {
                            $date = date('Y-m-d', strtotime("-{$i} days"));
                            $key = "email_stats:sent:{$type}:{$date}";
                            $total += Cache::get($key, 0);
                        }
                        $stats[$type] = $total;
                        break;
                    
                    case 'month':
                        $total = 0;
                        for ($i = 0; $i < 30; $i++) {
                            $date = date('Y-m-d', strtotime("-{$i} days"));
                            $key = "email_stats:sent:{$type}:{$date}";
                            $total += Cache::get($key, 0);
                        }
                        $stats[$type] = $total;
                        break;
                }
            }

            return $stats;
        } catch (Exception $e) {
            Log::error('Failed to retrieve email stats', [
                'period' => $period,
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    /**
     * Queue bulk notifications (for performance)
     */
    public function queueBulkNotifications(array $notifications): bool
    {
        try {
            foreach ($notifications as $notification) {
                Queue::push(function() use ($notification) {
                    switch ($notification['type']) {
                        case 'order_confirmation':
                            $this->sendOrderConfirmation($notification['order']);
                            break;
                        case 'welcome_email':
                            $this->sendWelcomeEmail($notification['user'], $notification['verification_required'] ?? false);
                            break;
                        // Add more types as needed
                    }
                });
            }

            Log::info('Bulk notifications queued', [
                'notification_count' => count($notifications)
            ]);

            return true;
        } catch (Exception $e) {
            Log::error('Failed to queue bulk notifications', [
                'error' => $e->getMessage(),
                'notification_count' => count($notifications)
            ]);
            return false;
        }
    }

    /**
     * Check if user has email notifications enabled
     */
    public function isEmailNotificationEnabled(User $user, string $notificationType): bool
    {
        // Default to enabled if no preference is set
        $preferences = $user->email_preferences ?? [];
        
        return $preferences[$notificationType] ?? true;
    }

    /**
     * Clear email analytics cache
     */
    public function clearEmailAnalytics(): bool
    {
        try {
            $patterns = [
                'email_stats:sent:*',
                'email_analytics:recent:*'
            ];

            foreach ($patterns as $pattern) {
                $keys = Cache::getRedis()->keys($pattern);
                if (!empty($keys)) {
                    Cache::getRedis()->del($keys);
                }
            }

            Log::info('Email analytics cache cleared');
            return true;
        } catch (Exception $e) {
            Log::error('Failed to clear email analytics cache', [
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Test email configuration
     */
    public function testEmailConfiguration(): array
    {
        $results = [
            'status' => 'success',
            'tests' => []
        ];

        try {
            // Test SMTP connection
            $results['tests']['smtp_connection'] = [
                'name' => 'SMTP Connection',
                'status' => 'success',
                'message' => 'Mail driver configured: ' . config('mail.default')
            ];

            // Test admin email settings
            $adminEmail = setting('admin_email', config('mail.from.address'));
            $results['tests']['admin_email'] = [
                'name' => 'Admin Email Configuration',
                'status' => $adminEmail ? 'success' : 'warning',
                'message' => $adminEmail ? "Admin email: {$adminEmail}" : 'Admin email not configured'
            ];

            // Test email templates
            $templates = [
                'emails.order.confirmation',
                'emails.order.status-update',
                'emails.admin.order-notification',
                'emails.user.welcome'
            ];

            foreach ($templates as $template) {
                $exists = view()->exists($template);
                $results['tests']['template_' . str_replace('.', '_', $template)] = [
                    'name' => "Template: {$template}",
                    'status' => $exists ? 'success' : 'error',
                    'message' => $exists ? 'Template exists' : 'Template missing'
                ];

                if (!$exists) {
                    $results['status'] = 'error';
                }
            }

        } catch (Exception $e) {
            $results['status'] = 'error';
            $results['error'] = $e->getMessage();
        }

        return $results;
    }
}
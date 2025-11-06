<?php

namespace App\Jobs;

use App\Models\User;
use App\Models\Order;
use App\Services\NotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Exception;

class SendNotificationEmailJob implements ShouldQueue
{
    use Queueable, InteractsWithQueue, SerializesModels;

    public int $tries = 3;
    public int $backoff = 60; // seconds
    public int $timeout = 120; // seconds

    public string $notificationType;
    public ?Order $order;
    public ?User $user;
    public array $options;

    /**
     * Create a new job instance.
     */
    public function __construct(
        string $notificationType,
        ?Order $order = null,
        ?User $user = null,
        array $options = []
    ) {
        $this->notificationType = $notificationType;
        $this->order = $order;
        $this->user = $user;
        $this->options = $options;
    }

    /**
     * Execute the job.
     */
    public function handle(NotificationService $notificationService): void
    {
        try {
            switch ($this->notificationType) {
                case 'order_confirmation':
                    if ($this->order) {
                        $notificationService->sendOrderConfirmation($this->order);
                    }
                    break;

                case 'order_status_update':
                    if ($this->order) {
                        $notificationService->sendOrderStatusUpdate(
                            $this->order,
                            $this->options['old_status'] ?? 'unknown',
                            $this->options['new_status'] ?? 'unknown',
                            $this->options['update_note'] ?? null
                        );
                    }
                    break;

                case 'admin_order_notification':
                    if ($this->order) {
                        $notificationService->sendAdminOrderNotification(
                            $this->order,
                            $this->options['admin_notification_type'] ?? 'new_order',
                            $this->options['additional_info'] ?? null
                        );
                    }
                    break;

                case 'welcome_email':
                    if ($this->user) {
                        $notificationService->sendWelcomeEmail(
                            $this->user,
                            $this->options['verification_required'] ?? false
                        );
                    }
                    break;

                case 'order_event_notifications':
                    if ($this->order) {
                        $notificationService->sendOrderEventNotifications(
                            $this->order,
                            $this->options['event'] ?? 'order_placed',
                            $this->options['event_options'] ?? []
                        );
                    }
                    break;

                default:
                    Log::warning('Unknown notification type in job', [
                        'notification_type' => $this->notificationType,
                        'order_id' => $this->order?->id,
                        'user_id' => $this->user?->id
                    ]);
                    return;
            }

            Log::info('Notification email job completed successfully', [
                'notification_type' => $this->notificationType,
                'order_id' => $this->order?->id,
                'user_id' => $this->user?->id
            ]);

        } catch (Exception $e) {
            Log::error('Notification email job failed', [
                'notification_type' => $this->notificationType,
                'order_id' => $this->order?->id,
                'user_id' => $this->user?->id,
                'error' => $e->getMessage(),
                'attempt' => $this->attempts()
            ]);

            // Re-throw to trigger retry mechanism
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(?Exception $exception = null): void
    {
        Log::error('Notification email job permanently failed', [
            'notification_type' => $this->notificationType,
            'order_id' => $this->order?->id,
            'user_id' => $this->user?->id,
            'error' => $exception?->getMessage(),
            'final_attempt' => $this->attempts()
        ]);

        // Could implement fallback mechanisms here:
        // - Store failed notifications in database for manual retry
        // - Send alert to admin about failed notification
        // - Use alternative notification channel (SMS, etc.)
    }

    /**
     * Determine the time at which the job should timeout.
     */
    public function retryUntil(): \DateTime
    {
        return now()->addHours(2);
    }

    /**
     * Calculate the number of seconds to wait before retrying the job.
     */
    public function backoff(): array
    {
        return [30, 60, 120]; // Wait 30s, 60s, 120s between retries
    }
}

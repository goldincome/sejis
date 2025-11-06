<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class LowStockAlertMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public Collection $lowStockItems;
    public array $alertSettings;
    public array $companySettings;

    /**
     * Create a new message instance.
     */
    public function __construct(Collection $lowStockItems, array $alertSettings = [])
    {
        $this->lowStockItems = $lowStockItems;
        $this->alertSettings = array_merge([
            'alert_level' => 'warning',
            'threshold_type' => 'below_minimum',
            'notification_frequency' => 'daily'
        ], $alertSettings);

        // Load company settings for email branding
        $this->companySettings = [
            'company_name' => setting('company_name', 'Sejis Kitchen Rental'),
            'company_email' => setting('company_email', 'admin@sejis.com'),
            'company_phone' => setting('company_phone', ''),
            'company_address' => setting('company_address', ''),
            'support_email' => setting('support_email', 'support@sejis.com'),
            'logo_url' => setting('company_logo_url', ''),
        ];
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $urgencyLevel = $this->determineUrgencyLevel();
        $subject = $this->buildSubject($urgencyLevel);

        return new Envelope(
            to: $this->getRecipients(),
            subject: $subject,
            tags: ['inventory', 'low-stock', 'alert', $urgencyLevel],
            metadata: [
                'alert_type' => 'low_stock',
                'urgency' => $urgencyLevel,
                'item_count' => $this->lowStockItems->count(),
                'notification_frequency' => $this->alertSettings['notification_frequency']
            ]
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.inventory.low-stock-alert',
            with: [
                'lowStockItems' => $this->lowStockItems,
                'alertSettings' => $this->alertSettings,
                'companySettings' => $this->companySettings,
                'urgencyLevel' => $this->determineUrgencyLevel(),
                'totalItemsAffected' => $this->lowStockItems->count(),
                'criticalItems' => $this->getCriticalItems(),
                'actionRequired' => $this->getActionRequired(),
                'dashboardUrl' => route('admin.inventory.index'),
                'itemsUrl' => route('admin.inventory.items', ['status' => 'low_stock'])
            ]
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }

    /**
     * Determine urgency level based on stock levels
     */
    private function determineUrgencyLevel(): string
    {
        $criticalCount = $this->lowStockItems->filter(function ($item) {
            return $item->quantity_on_hand <= 0 || 
                   ($item->minimum_stock_level && $item->quantity_on_hand <= $item->minimum_stock_level * 0.5);
        })->count();

        $urgentCount = $this->lowStockItems->filter(function ($item) {
            return $item->minimum_stock_level && 
                   $item->quantity_on_hand <= $item->minimum_stock_level * 0.75;
        })->count();

        if ($criticalCount > 0) {
            return 'critical';
        } elseif ($urgentCount > 0) {
            return 'urgent';
        } else {
            return 'warning';
        }
    }

    /**
     * Build subject line based on urgency
     */
    private function buildSubject(string $urgencyLevel): string
    {
        $itemCount = $this->lowStockItems->count();
        $companyName = $this->companySettings['company_name'];

        return match ($urgencyLevel) {
            'critical' => "🚨 CRITICAL: {$itemCount} Items Out of Stock - {$companyName}",
            'urgent' => "⚠️ URGENT: {$itemCount} Items Low on Stock - {$companyName}",
            'warning' => "📦 Low Stock Alert: {$itemCount} Items Need Attention - {$companyName}",
            default => "📦 Inventory Alert: {$itemCount} Items - {$companyName}"
        };
    }

    /**
     * Get email recipients based on settings
     */
    private function getRecipients(): array
    {
        // Default recipients - in a real system this would come from settings
        $defaultRecipients = [
            setting('inventory_manager_email', 'manager@sejis.com'),
            setting('admin_email', 'admin@sejis.com')
        ];

        // Add additional recipients based on urgency
        $urgencyLevel = $this->determineUrgencyLevel();
        if ($urgencyLevel === 'critical') {
            $defaultRecipients[] = setting('ceo_email', 'ceo@sejis.com');
            $defaultRecipients[] = setting('operations_director_email', 'operations@sejis.com');
        }

        return array_filter($defaultRecipients);
    }

    /**
     * Get items that require critical attention
     */
    private function getCriticalItems(): Collection
    {
        return $this->lowStockItems->filter(function ($item) {
            return $item->quantity_on_hand <= 0 || 
                   ($item->minimum_stock_level && $item->quantity_on_hand <= $item->minimum_stock_level * 0.25);
        });
    }

    /**
     * Get recommended actions
     */
    private function getActionRequired(): array
    {
        $actions = [];
        $criticalItems = $this->getCriticalItems();
        
        if ($criticalItems->count() > 0) {
            $actions[] = [
                'priority' => 'high',
                'action' => 'Immediate restocking required',
                'description' => $criticalItems->count() . ' items are critically low or out of stock',
                'url' => route('admin.inventory.items', ['status' => 'out_of_stock'])
            ];
        }

        $lowStockItems = $this->lowStockItems->filter(function ($item) {
            return $item->quantity_on_hand > 0 && 
                   $item->minimum_stock_level && 
                   $item->quantity_on_hand <= $item->minimum_stock_level;
        });

        if ($lowStockItems->count() > 0) {
            $actions[] = [
                'priority' => 'medium',
                'action' => 'Plan restocking',
                'description' => $lowStockItems->count() . ' items are below minimum threshold',
                'url' => route('admin.inventory.items', ['status' => 'low_stock'])
            ];
        }

        $maintenanceDue = $this->lowStockItems->filter(function ($item) {
            return $item->isMaintenanceDue() || $item->isMaintenanceOverdue();
        });

        if ($maintenanceDue->count() > 0) {
            $actions[] = [
                'priority' => 'medium',
                'action' => 'Schedule maintenance',
                'description' => $maintenanceDue->count() . ' items require maintenance',
                'url' => route('admin.inventory.items', ['status' => 'maintenance_due'])
            ];
        }

        return $actions;
    }

    /**
     * Get priority level for queue processing
     */
    public function getQueuePriority(): int
    {
        return match ($this->determineUrgencyLevel()) {
            'critical' => 1,  // Highest priority
            'urgent' => 5,
            'warning' => 10,
            default => 15
        };
    }

    /**
     * Set the queue for this job based on urgency
     */
    public function viaQueues(): array
    {
        $urgencyLevel = $this->determineUrgencyLevel();
        
        return [
            'mail' => $urgencyLevel === 'critical' ? 'high-priority' : 'default'
        ];
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        \Log::error('Low stock alert email failed', [
            'item_count' => $this->lowStockItems->count(),
            'urgency' => $this->determineUrgencyLevel(),
            'error' => $exception->getMessage(),
            'recipients' => $this->getRecipients()
        ]);
    }
}
<?php

namespace App\Console\Commands;

use App\Services\InventoryService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Exception;

class SendLowStockAlerts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'inventory:send-low-stock-alerts 
                           {--force : Force send alerts even if frequency limits are active}
                           {--frequency=daily : Set notification frequency (daily, weekly, hourly)}
                           {--urgency= : Filter by urgency level (critical, urgent, warning)}
                           {--dry-run : Preview what alerts would be sent without actually sending}
                           {--recipients=* : Override default recipients with custom email addresses}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send low stock alert notifications to configured recipients based on inventory levels';

    protected InventoryService $inventoryService;

    /**
     * Create a new command instance.
     */
    public function __construct(InventoryService $inventoryService)
    {
        parent::__construct();
        $this->inventoryService = $inventoryService;
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🔍 Scanning inventory for low stock items...');
        
        try {
            // Gather command options
            $options = $this->gatherOptions();
            
            if ($options['dry_run']) {
                return $this->performDryRun($options);
            }
            
            // Get low stock items
            $lowStockItems = $this->inventoryService->getLowStockItemsCollection();
            
            if ($lowStockItems->isEmpty()) {
                $this->info('✅ No low stock items found. All inventory levels are adequate.');
                return self::SUCCESS;
            }
            
            $this->info("📦 Found {$lowStockItems->count()} items with low stock levels.");
            
            // Apply urgency filter if specified
            if ($options['urgency']) {
                $lowStockItems = $this->filterByUrgency($lowStockItems, $options['urgency']);
                $this->info("🎯 Filtered to {$lowStockItems->count()} items matching urgency: {$options['urgency']}");
            }
            
            if ($lowStockItems->isEmpty()) {
                $this->info('ℹ️ No items match the specified urgency filter.');
                return self::SUCCESS;
            }
            
            // Send alerts
            $alertSettings = $this->buildAlertSettings($options);
            
            if ($options['force']) {
                $result = $this->inventoryService->forceSendLowStockAlerts($alertSettings);
                $this->warn('⚠️ Force mode: Ignoring frequency limitations.');
            } else {
                $result = $this->inventoryService->sendLowStockAlerts($alertSettings);
            }
            
            if ($result) {
                $this->info('✅ Low stock alerts sent successfully.');
                
                // Log summary
                $this->displaySummary($lowStockItems, $options);
                
                Log::info('Low stock alerts command completed successfully', [
                    'items_count' => $lowStockItems->count(),
                    'options' => $options,
                    'command_run_at' => now()->toDateTimeString()
                ]);
                
                return self::SUCCESS;
            } else {
                $this->error('❌ Failed to send low stock alerts. Check logs for details.');
                return self::FAILURE;
            }
            
        } catch (Exception $e) {
            $this->error('💥 Error occurred while processing low stock alerts: ' . $e->getMessage());
            
            Log::error('Low stock alerts command failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'command_run_at' => now()->toDateTimeString()
            ]);
            
            return self::FAILURE;
        }
    }
    
    /**
     * Gather and validate command options
     */
    private function gatherOptions(): array
    {
        $options = [
            'force' => $this->option('force'),
            'frequency' => $this->option('frequency'),
            'urgency' => $this->option('urgency'),
            'dry_run' => $this->option('dry-run'),
            'recipients' => $this->option('recipients')
        ];
        
        // Validate frequency
        if (!in_array($options['frequency'], ['daily', 'weekly', 'hourly'])) {
            $this->error('Invalid frequency. Must be one of: daily, weekly, hourly');
            exit(self::INVALID);
        }
        
        // Validate urgency if provided
        if ($options['urgency'] && !in_array($options['urgency'], ['critical', 'urgent', 'warning'])) {
            $this->error('Invalid urgency level. Must be one of: critical, urgent, warning');
            exit(self::INVALID);
        }
        
        return $options;
    }
    
    /**
     * Perform dry run to preview alerts
     */
    private function performDryRun(array $options): int
    {
        $this->warn('🔍 DRY RUN MODE - No emails will be sent');
        
        $lowStockItems = $this->inventoryService->getLowStockItemsCollection();
        
        if ($lowStockItems->isEmpty()) {
            $this->info('✅ No low stock items found.');
            return self::SUCCESS;
        }
        
        $this->info("📦 Would process {$lowStockItems->count()} low stock items:");
        
        // Group by urgency
        $critical = $lowStockItems->filter(function ($item) {
            return $item->quantity_on_hand <= 0 || 
                   ($item->minimum_stock_level && $item->quantity_on_hand <= $item->minimum_stock_level * 0.5);
        });
        
        $urgent = $lowStockItems->filter(function ($item) {
            return $item->minimum_stock_level && 
                   $item->quantity_on_hand <= $item->minimum_stock_level * 0.75 &&
                   $item->quantity_on_hand > $item->minimum_stock_level * 0.5;
        });
        
        $warning = $lowStockItems->diff($critical)->diff($urgent);
        
        if ($critical->count() > 0) {
            $this->error("🚨 CRITICAL: {$critical->count()} items");
            $this->table(['SKU', 'Name', 'Current Stock', 'Min Threshold'], 
                $critical->take(5)->map(fn($item) => [
                    $item->sku,
                    $item->name,
                    $item->quantity_on_hand,
                    $item->minimum_stock_level ?? 'Not set'
                ])->toArray()
            );
        }
        
        if ($urgent->count() > 0) {
            $this->warn("⚠️ URGENT: {$urgent->count()} items");
        }
        
        if ($warning->count() > 0) {
            $this->info("📋 WARNING: {$warning->count()} items");
        }
        
        $recipients = $options['recipients'] ?: [
            setting('inventory_manager_email', 'manager@sejis.com'),
            setting('admin_email', 'admin@sejis.com')
        ];
        
        $this->info('📧 Would send alerts to: ' . implode(', ', array_filter($recipients)));
        
        return self::SUCCESS;
    }
    
    /**
     * Filter items by urgency level
     */
    private function filterByUrgency($items, string $urgency)
    {
        return $items->filter(function ($item) use ($urgency) {
            $itemUrgency = 'warning';
            
            if ($item->quantity_on_hand <= 0 || 
                ($item->minimum_stock_level && $item->quantity_on_hand <= $item->minimum_stock_level * 0.5)) {
                $itemUrgency = 'critical';
            } elseif ($item->minimum_stock_level && 
                     $item->quantity_on_hand <= $item->minimum_stock_level * 0.75) {
                $itemUrgency = 'urgent';
            }
            
            return $itemUrgency === $urgency;
        });
    }
    
    /**
     * Build alert settings from command options
     */
    private function buildAlertSettings(array $options): array
    {
        $settings = [
            'notification_frequency' => $options['frequency'],
            'alert_level' => 'warning',
            'threshold_type' => 'below_minimum'
        ];
        
        // Add custom recipients if provided
        if (!empty($options['recipients'])) {
            $settings['custom_recipients'] = $options['recipients'];
        }
        
        return $settings;
    }
    
    /**
     * Display summary of processed items
     */
    private function displaySummary($lowStockItems, array $options): void
    {
        $this->newLine();
        $this->info('📊 ALERT SUMMARY');
        $this->line('─────────────────');
        
        // Count by urgency
        $critical = $lowStockItems->filter(function ($item) {
            return $item->quantity_on_hand <= 0 || 
                   ($item->minimum_stock_level && $item->quantity_on_hand <= $item->minimum_stock_level * 0.5);
        })->count();
        
        $urgent = $lowStockItems->filter(function ($item) {
            return $item->minimum_stock_level && 
                   $item->quantity_on_hand <= $item->minimum_stock_level * 0.75 &&
                   $item->quantity_on_hand > ($item->minimum_stock_level * 0.5);
        })->count();
        
        $warning = $lowStockItems->count() - $critical - $urgent;
        
        $this->line("🚨 Critical Items: {$critical}");
        $this->line("⚠️ Urgent Items: {$urgent}");
        $this->line("📋 Warning Items: {$warning}");
        $this->line("📦 Total Items: {$lowStockItems->count()}");
        $this->line("📧 Frequency: {$options['frequency']}");
        $this->line("⏰ Sent At: " . now()->format('Y-m-d H:i:s T'));
        
        if ($options['force']) {
            $this->line("⚠️ Force Mode: ON");
        }
        
        $this->newLine();
    }
}

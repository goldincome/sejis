<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\PaymentAuditService;
use App\Models\PaymentAuditLog;
use App\Models\WebhookLog;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;

class PaymentMonitoringController extends Controller
{
    protected $auditService;

    public function __construct(PaymentAuditService $auditService)
    {
        $this->auditService = $auditService;
    }

    /**
     * Display payment monitoring dashboard
     */
    public function index(Request $request)
    {
        $startDate = $request->input('start_date') ? Carbon::parse($request->input('start_date')) : now()->startOfMonth();
        $endDate = $request->input('end_date') ? Carbon::parse($request->input('end_date')) : now()->endOfMonth();

        $stats = $this->auditService->getPaymentStats($startDate, $endDate);
        $gatewayStats = $this->auditService->getGatewayStats($startDate, $endDate);
        $suspiciousActivities = $this->auditService->getRecentSuspiciousActivities(10);
        $failureAnalysis = $this->auditService->getFailureAnalysis($startDate, $endDate);

        // Recent webhook logs
        $recentWebhooks = WebhookLog::recent(24)
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();

        return view('admin.payment-monitoring.index', compact(
            'stats', 
            'gatewayStats', 
            'suspiciousActivities', 
            'failureAnalysis',
            'recentWebhooks',
            'startDate',
            'endDate'
        ));
    }

    /**
     * Get payment audit logs with filtering
     */
    public function auditLogs(Request $request): JsonResponse
    {
        $query = PaymentAuditLog::with(['order', 'user'])
            ->orderBy('created_at', 'desc');

        // Apply filters
        if ($request->filled('gateway')) {
            $query->forGateway($request->gateway);
        }

        if ($request->filled('status')) {
            $query->withStatus($request->status);
        }

        if ($request->filled('event_type')) {
            $query->forEvent($request->event_type);
        }

        if ($request->filled('start_date')) {
            $startDate = Carbon::parse($request->start_date);
            $endDate = $request->filled('end_date') 
                ? Carbon::parse($request->end_date) 
                : now();
            
            $query->dateRange($startDate, $endDate);
        }

        if ($request->filled('order_id')) {
            $query->where('order_id', $request->order_id);
        }

        if ($request->filled('transaction_id')) {
            $query->where('transaction_id', 'like', '%' . $request->transaction_id . '%');
        }

        $logs = $query->paginate(50);

        return response()->json($logs);
    }

    /**
     * Get webhook logs with filtering
     */
    public function webhookLogs(Request $request): JsonResponse
    {
        $query = WebhookLog::orderBy('created_at', 'desc');

        // Apply filters
        if ($request->filled('gateway')) {
            $query->forGateway($request->gateway);
        }

        if ($request->filled('status')) {
            $query->withStatus($request->status);
        }

        if ($request->filled('event_type')) {
            $query->where('event_type', 'like', '%' . $request->event_type . '%');
        }

        if ($request->filled('hours')) {
            $query->recent($request->hours);
        }

        $logs = $query->paginate(50);

        return response()->json($logs);
    }

    /**
     * Get real-time payment statistics
     */
    public function realtimeStats(): JsonResponse
    {
        $stats = [
            'today' => $this->auditService->getPaymentStats(now()->startOfDay(), now()),
            'last_hour' => [
                'attempts' => PaymentAuditLog::forEvent('payment_attempt')
                    ->where('created_at', '>=', now()->subHour())
                    ->count(),
                'successes' => PaymentAuditLog::forEvent('payment_success')
                    ->where('created_at', '>=', now()->subHour())
                    ->count(),
                'failures' => PaymentAuditLog::forEvent('payment_failed')
                    ->where('created_at', '>=', now()->subHour())
                    ->count(),
            ],
            'suspicious_last_24h' => PaymentAuditLog::suspicious()
                ->recent(24)
                ->count(),
            'active_webhooks_last_hour' => WebhookLog::recent(1)->count()
        ];

        return response()->json($stats);
    }

    /**
     * Get payment trends data for charts
     */
    public function paymentTrends(Request $request): JsonResponse
    {
        $startDate = $request->input('start_date') ? Carbon::parse($request->input('start_date')) : now()->subDays(30);
        $endDate = $request->input('end_date') ? Carbon::parse($request->input('end_date')) : now();
        $groupBy = $request->input('group_by', 'day'); // day, hour, week

        $query = PaymentAuditLog::forEvent('payment_success')
            ->dateRange($startDate, $endDate);

        switch ($groupBy) {
            case 'hour':
                $data = $query->selectRaw('DATE_FORMAT(created_at, "%Y-%m-%d %H:00:00") as period, COUNT(*) as count, SUM(amount) as total')
                    ->groupByRaw('DATE_FORMAT(created_at, "%Y-%m-%d %H:00:00")')
                    ->orderBy('period')
                    ->get();
                break;
            case 'week':
                $data = $query->selectRaw('YEARWEEK(created_at) as period, COUNT(*) as count, SUM(amount) as total')
                    ->groupBy('period')
                    ->orderBy('period')
                    ->get();
                break;
            default:
                $data = $query->selectRaw('DATE(created_at) as period, COUNT(*) as count, SUM(amount) as total')
                    ->groupBy('period')
                    ->orderBy('period')
                    ->get();
        }

        return response()->json($data);
    }

    /**
     * Export payment data
     */
    public function exportData(Request $request)
    {
        $startDate = $request->input('start_date') ? Carbon::parse($request->input('start_date')) : now()->startOfMonth();
        $endDate = $request->input('end_date') ? Carbon::parse($request->input('end_date')) : now();
        $type = $request->input('type', 'payments'); // payments, webhooks, audit

        $filename = "payment_{$type}_" . $startDate->format('Y-m-d') . '_to_' . $endDate->format('Y-m-d') . '.csv';

        switch ($type) {
            case 'webhooks':
                return $this->exportWebhookData($startDate, $endDate, $filename);
            case 'audit':
                return $this->exportAuditData($startDate, $endDate, $filename);
            default:
                return $this->exportPaymentData($startDate, $endDate, $filename);
        }
    }

    /**
     * Export payment data to CSV
     */
    protected function exportPaymentData(Carbon $startDate, Carbon $endDate, string $filename)
    {
        $data = PaymentAuditLog::with(['order', 'user'])
            ->dateRange($startDate, $endDate)
            ->orderBy('created_at', 'desc')
            ->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\""
        ];

        $callback = function() use ($data) {
            $handle = fopen('php://output', 'w');
            
            // CSV headers
            fputcsv($handle, [
                'ID', 'Order ID', 'User Email', 'Gateway', 'Transaction ID', 
                'Event Type', 'Amount', 'Currency', 'Status', 'Error Message',
                'IP Address', 'Created At'
            ]);

            // CSV data
            foreach ($data as $log) {
                fputcsv($handle, [
                    $log->id,
                    $log->order_id,
                    $log->user->email ?? 'N/A',
                    $log->gateway,
                    $log->transaction_id,
                    $log->event_type,
                    $log->amount,
                    $log->currency,
                    $log->status,
                    $log->error_message,
                    $log->ip_address,
                    $log->created_at->format('Y-m-d H:i:s')
                ]);
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export webhook data to CSV
     */
    protected function exportWebhookData(Carbon $startDate, Carbon $endDate, string $filename)
    {
        $data = WebhookLog::whereBetween('created_at', [$startDate, $endDate])
            ->orderBy('created_at', 'desc')
            ->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\""
        ];

        $callback = function() use ($data) {
            $handle = fopen('php://output', 'w');
            
            fputcsv($handle, [
                'ID', 'Gateway', 'Event ID', 'Event Type', 'Status', 
                'Error Message', 'Processed At', 'Created At'
            ]);

            foreach ($data as $log) {
                fputcsv($handle, [
                    $log->id,
                    $log->gateway,
                    $log->event_id,
                    $log->event_type,
                    $log->status,
                    $log->error_message,
                    $log->processed_at?->format('Y-m-d H:i:s'),
                    $log->created_at->format('Y-m-d H:i:s')
                ]);
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export audit data to CSV
     */
    protected function exportAuditData(Carbon $startDate, Carbon $endDate, string $filename)
    {
        $data = PaymentAuditLog::suspicious()
            ->dateRange($startDate, $endDate)
            ->with(['order', 'user'])
            ->orderBy('created_at', 'desc')
            ->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\""
        ];

        $callback = function() use ($data) {
            $handle = fopen('php://output', 'w');
            
            fputcsv($handle, [
                'ID', 'User Email', 'Gateway', 'Event Type', 'Status',
                'Error Message', 'IP Address', 'User Agent', 'Created At'
            ]);

            foreach ($data as $log) {
                fputcsv($handle, [
                    $log->id,
                    $log->user->email ?? 'N/A',
                    $log->gateway,
                    $log->event_type,
                    $log->status,
                    $log->error_message,
                    $log->ip_address,
                    $log->user_agent,
                    $log->created_at->format('Y-m-d H:i:s')
                ]);
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }
}
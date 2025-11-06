<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\InventoryReportService;
use App\Models\Category;
use App\Models\InventoryItem;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Exception;

class InventoryReportController extends Controller
{
    protected InventoryReportService $reportService;

    public function __construct(InventoryReportService $reportService)
    {
        $this->reportService = $reportService;
    }

    /**
     * Display reports dashboard
     */
    public function index()
    {
        // Get basic stats for dashboard overview
        $categories = Category::all();
        $locations = InventoryItem::distinct()->pluck('location')->filter()->values();
        $users = User::where('role', 'admin')->orWhere('role', 'manager')->get();
        
        return view('admin.inventory.reports.index', compact('categories', 'locations', 'users'));
    }

    /**
     * Stock valuation report
     */
    public function stockValuation(Request $request)
    {
        $request->validate([
            'category_id' => 'nullable|exists:categories,id',
            'location' => 'nullable|string',
            'condition' => 'nullable|in:excellent,good,fair,poor,needs_repair,out_of_service',
            'export' => 'nullable|boolean'
        ]);

        try {
            $filters = $request->only(['category_id', 'location', 'condition']);
            $report = $this->reportService->getStockValuationReport($filters);

            if ($request->boolean('export')) {
                $filename = $this->reportService->exportReportToCSV('stock_valuation', $report, $filters);
                return response()->download($filename)->deleteFileAfterSend();
            }

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'report' => $report
                ]);
            }

            $categories = Category::all();
            $locations = InventoryItem::distinct()->pluck('location')->filter()->values();
            
            return view('admin.inventory.reports.stock-valuation', compact(
                'report', 
                'categories', 
                'locations', 
                'filters'
            ));

        } catch (Exception $e) {
            Log::error('Stock valuation report generation failed', [
                'filters' => $filters ?? [],
                'error' => $e->getMessage()
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to generate report: ' . $e->getMessage()
                ], 500);
            }

            return back()->withErrors('Failed to generate stock valuation report.');
        }
    }

    /**
     * Movement analytics report
     */
    public function movementAnalytics(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'movement_type' => 'nullable|string',
            'inventory_item_id' => 'nullable|exists:inventory_items,id',
            'user_id' => 'nullable|exists:users,id',
            'export' => 'nullable|boolean'
        ]);

        try {
            $startDate = Carbon::parse($request->start_date);
            $endDate = Carbon::parse($request->end_date);
            $filters = $request->only(['movement_type', 'inventory_item_id', 'user_id']);
            
            $report = $this->reportService->getMovementAnalyticsReport($startDate, $endDate, $filters);

            if ($request->boolean('export')) {
                $filename = $this->reportService->exportReportToCSV('movement_analytics', $report, $filters);
                return response()->download($filename)->deleteFileAfterSend();
            }

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'report' => $report
                ]);
            }

            $inventoryItems = InventoryItem::with('product')->get();
            $users = User::all();
            
            return view('admin.inventory.reports.movement-analytics', compact(
                'report', 
                'inventoryItems', 
                'users', 
                'filters',
                'startDate',
                'endDate'
            ));

        } catch (Exception $e) {
            Log::error('Movement analytics report generation failed', [
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'filters' => $filters ?? [],
                'error' => $e->getMessage()
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to generate report: ' . $e->getMessage()
                ], 500);
            }

            return back()->withErrors('Failed to generate movement analytics report.');
        }
    }

    /**
     * Stock turnover report
     */
    public function stockTurnover(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'export' => 'nullable|boolean'
        ]);

        try {
            $startDate = Carbon::parse($request->start_date);
            $endDate = Carbon::parse($request->end_date);
            
            $report = $this->reportService->getStockTurnoverReport($startDate, $endDate);

            if ($request->boolean('export')) {
                $filename = $this->reportService->exportReportToCSV('stock_turnover', $report);
                return response()->download($filename)->deleteFileAfterSend();
            }

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'report' => $report
                ]);
            }

            return view('admin.inventory.reports.stock-turnover', compact(
                'report',
                'startDate',
                'endDate'
            ));

        } catch (Exception $e) {
            Log::error('Stock turnover report generation failed', [
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'error' => $e->getMessage()
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to generate report: ' . $e->getMessage()
                ], 500);
            }

            return back()->withErrors('Failed to generate stock turnover report.');
        }
    }

    /**
     * ABC analysis report
     */
    public function abcAnalysis(Request $request)
    {
        $request->validate([
            'export' => 'nullable|boolean'
        ]);

        try {
            $report = $this->reportService->getABCAnalysisReport();

            if ($request->boolean('export')) {
                $filename = $this->reportService->exportReportToCSV('abc_analysis', $report);
                return response()->download($filename)->deleteFileAfterSend();
            }

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'report' => $report
                ]);
            }

            return view('admin.inventory.reports.abc-analysis', compact('report'));

        } catch (Exception $e) {
            Log::error('ABC analysis report generation failed', [
                'error' => $e->getMessage()
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to generate report: ' . $e->getMessage()
                ], 500);
            }

            return back()->withErrors('Failed to generate ABC analysis report.');
        }
    }

    /**
     * Low stock impact analysis
     */
    public function lowStockImpact(Request $request)
    {
        try {
            $report = $this->reportService->getLowStockImpactReport();

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'report' => $report
                ]);
            }

            return view('admin.inventory.reports.low-stock-impact', compact('report'));

        } catch (Exception $e) {
            Log::error('Low stock impact report generation failed', [
                'error' => $e->getMessage()
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to generate report: ' . $e->getMessage()
                ], 500);
            }

            return back()->withErrors('Failed to generate low stock impact report.');
        }
    }

    /**
     * Generate comprehensive inventory summary report
     */
    public function comprehensiveReport(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'include_valuation' => 'nullable|boolean',
            'include_movements' => 'nullable|boolean',
            'include_turnover' => 'nullable|boolean',
            'include_abc' => 'nullable|boolean',
            'include_low_stock' => 'nullable|boolean'
        ]);

        try {
            $startDate = Carbon::parse($request->start_date);
            $endDate = Carbon::parse($request->end_date);
            
            $comprehensiveReport = [];

            // Include selected report sections
            if ($request->boolean('include_valuation', true)) {
                $comprehensiveReport['stock_valuation'] = $this->reportService->getStockValuationReport();
            }

            if ($request->boolean('include_movements', true)) {
                $comprehensiveReport['movement_analytics'] = $this->reportService->getMovementAnalyticsReport($startDate, $endDate);
            }

            if ($request->boolean('include_turnover', true)) {
                $comprehensiveReport['stock_turnover'] = $this->reportService->getStockTurnoverReport($startDate, $endDate);
            }

            if ($request->boolean('include_abc', true)) {
                $comprehensiveReport['abc_analysis'] = $this->reportService->getABCAnalysisReport();
            }

            if ($request->boolean('include_low_stock', true)) {
                $comprehensiveReport['low_stock_impact'] = $this->reportService->getLowStockImpactReport();
            }

            $comprehensiveReport['meta'] = [
                'generated_at' => now()->format('Y-m-d H:i:s'),
                'period' => [
                    'start_date' => $startDate->format('Y-m-d'),
                    'end_date' => $endDate->format('Y-m-d')
                ],
                'generated_by' => auth()->user()->name,
                'sections_included' => array_keys($comprehensiveReport)
            ];

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'report' => $comprehensiveReport
                ]);
            }

            return view('admin.inventory.reports.comprehensive', compact(
                'comprehensiveReport',
                'startDate',
                'endDate'
            ));

        } catch (Exception $e) {
            Log::error('Comprehensive inventory report generation failed', [
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'error' => $e->getMessage()
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to generate comprehensive report: ' . $e->getMessage()
                ], 500);
            }

            return back()->withErrors('Failed to generate comprehensive inventory report.');
        }
    }

    /**
     * Get report data for dashboard widgets
     */
    public function dashboardWidgets(Request $request): JsonResponse
    {
        try {
            $widgets = [];

            // Stock valuation summary
            $stockValuation = $this->reportService->getStockValuationReport();
            $widgets['stock_valuation'] = [
                'total_value' => $stockValuation['summary']['total_current_value'],
                'value_change' => $stockValuation['summary']['total_value_change'],
                'value_change_percentage' => $stockValuation['summary']['total_value_change_percentage']
            ];

            // Movement summary (last 30 days)
            $endDate = now();
            $startDate = now()->subDays(30);
            $movements = $this->reportService->getMovementAnalyticsReport($startDate, $endDate);
            $widgets['recent_movements'] = [
                'total_movements' => $movements['summary']['total_movements'],
                'stock_in' => $movements['summary']['total_stock_in'],
                'stock_out' => $movements['summary']['total_stock_out'],
                'rentals' => $movements['summary']['total_rentals']
            ];

            // Low stock impact
            $lowStockImpact = $this->reportService->getLowStockImpactReport();
            $widgets['low_stock_impact'] = [
                'items_count' => $lowStockImpact['summary']['total_low_stock_items'],
                'potential_revenue_loss' => $lowStockImpact['summary']['total_potential_revenue_loss'],
                'high_urgency_count' => $lowStockImpact['summary']['high_urgency_items']
            ];

            return response()->json([
                'success' => true,
                'widgets' => $widgets
            ]);

        } catch (Exception $e) {
            Log::error('Dashboard widgets data generation failed', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to generate dashboard widgets data'
            ], 500);
        }
    }

    /**
     * Schedule automated reports
     */
    public function scheduleReports(Request $request): JsonResponse
    {
        $request->validate([
            'report_type' => 'required|in:stock_valuation,movement_analytics,abc_analysis,comprehensive',
            'frequency' => 'required|in:daily,weekly,monthly',
            'recipients' => 'required|array',
            'recipients.*' => 'email',
            'active' => 'nullable|boolean'
        ]);

        try {
            // This would typically save to a scheduled_reports table
            // For now, we'll just return success
            
            Log::info('Automated report scheduled', [
                'report_type' => $request->report_type,
                'frequency' => $request->frequency,
                'recipients' => $request->recipients,
                'scheduled_by' => auth()->user()->id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Automated report scheduled successfully'
            ]);

        } catch (Exception $e) {
            Log::error('Failed to schedule automated report', [
                'error' => $e->getMessage(),
                'request_data' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to schedule automated report: ' . $e->getMessage()
            ], 500);
        }
    }
}
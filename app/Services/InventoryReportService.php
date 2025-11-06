<?php

namespace App\Services;

use App\Models\InventoryItem;
use App\Models\InventoryMovement;
use App\Models\Product;
use App\Models\Category;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Collection;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class InventoryReportService
{
    /**
     * Generate comprehensive stock valuation report
     */
    public function getStockValuationReport(array $filters = []): array
    {
        $cacheKey = 'stock_valuation_report_' . md5(serialize($filters));
        
        return Cache::remember($cacheKey, 900, function () use ($filters) {
            $query = InventoryItem::active()->with(['product.category']);
            
            // Apply filters
            if (!empty($filters['category_id'])) {
                $query->whereHas('product', function ($q) use ($filters) {
                    $q->where('category_id', $filters['category_id']);
                });
            }
            
            if (!empty($filters['location'])) {
                $query->where('location', $filters['location']);
            }
            
            if (!empty($filters['condition'])) {
                $query->where('condition', $filters['condition']);
            }
            
            $items = $query->get();
            
            $totalItems = $items->count();
            $totalPurchaseValue = $items->sum(function ($item) {
                return ($item->purchase_cost ?? 0) * $item->quantity_on_hand;
            });
            $totalCurrentValue = $items->sum(function ($item) {
                return ($item->current_value ?? $item->purchase_cost ?? 0) * $item->quantity_on_hand;
            });
            
            // Group by category
            $categoryBreakdown = $items->groupBy('product.category.name')->map(function ($categoryItems, $categoryName) {
                $itemCount = $categoryItems->count();
                $totalQuantity = $categoryItems->sum('quantity_on_hand');
                $purchaseValue = $categoryItems->sum(function ($item) {
                    return ($item->purchase_cost ?? 0) * $item->quantity_on_hand;
                });
                $currentValue = $categoryItems->sum(function ($item) {
                    return ($item->current_value ?? $item->purchase_cost ?? 0) * $item->quantity_on_hand;
                });
                
                return [
                    'category_name' => $categoryName ?: 'Uncategorized',
                    'item_count' => $itemCount,
                    'total_quantity' => $totalQuantity,
                    'purchase_value' => $purchaseValue,
                    'current_value' => $currentValue,
                    'value_change' => $currentValue - $purchaseValue,
                    'value_change_percentage' => $purchaseValue > 0 ? round((($currentValue - $purchaseValue) / $purchaseValue) * 100, 2) : 0
                ];
            })->sortByDesc('current_value')->values();
            
            // Group by location
            $locationBreakdown = $items->groupBy('location')->map(function ($locationItems, $location) {
                $itemCount = $locationItems->count();
                $totalQuantity = $locationItems->sum('quantity_on_hand');
                $currentValue = $locationItems->sum(function ($item) {
                    return ($item->current_value ?? $item->purchase_cost ?? 0) * $item->quantity_on_hand;
                });
                
                return [
                    'location' => $location ?: 'Unassigned',
                    'item_count' => $itemCount,
                    'total_quantity' => $totalQuantity,
                    'current_value' => $currentValue
                ];
            })->sortByDesc('current_value')->values();
            
            // Condition analysis
            $conditionBreakdown = $items->groupBy('condition')->map(function ($conditionItems, $condition) {
                $itemCount = $conditionItems->count();
                $totalQuantity = $conditionItems->sum('quantity_on_hand');
                $currentValue = $conditionItems->sum(function ($item) {
                    return ($item->current_value ?? $item->purchase_cost ?? 0) * $item->quantity_on_hand;
                });
                
                return [
                    'condition' => ucfirst($condition),
                    'item_count' => $itemCount,
                    'total_quantity' => $totalQuantity,
                    'current_value' => $currentValue,
                    'percentage_of_total' => $totalItems > 0 ? round(($itemCount / $totalItems) * 100, 1) : 0
                ];
            });
            
            return [
                'summary' => [
                    'total_items' => $totalItems,
                    'total_purchase_value' => $totalPurchaseValue,
                    'total_current_value' => $totalCurrentValue,
                    'total_value_change' => $totalCurrentValue - $totalPurchaseValue,
                    'total_value_change_percentage' => $totalPurchaseValue > 0 ? round((($totalCurrentValue - $totalPurchaseValue) / $totalPurchaseValue) * 100, 2) : 0,
                    'average_item_value' => $totalItems > 0 ? round($totalCurrentValue / $totalItems, 2) : 0
                ],
                'category_breakdown' => $categoryBreakdown,
                'location_breakdown' => $locationBreakdown,
                'condition_breakdown' => $conditionBreakdown,
                'generated_at' => now()->format('Y-m-d H:i:s')
            ];
        });
    }
    
    /**
     * Generate inventory movement analytics report
     */
    public function getMovementAnalyticsReport(Carbon $startDate, Carbon $endDate, array $filters = []): array
    {
        $query = InventoryMovement::with(['inventoryItem.product.category', 'user'])
            ->whereBetween('movement_date', [$startDate, $endDate]);
        
        // Apply filters
        if (!empty($filters['movement_type'])) {
            $query->where('movement_type', $filters['movement_type']);
        }
        
        if (!empty($filters['inventory_item_id'])) {
            $query->where('inventory_item_id', $filters['inventory_item_id']);
        }
        
        if (!empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }
        
        $movements = $query->get();
        
        // Movement type analysis
        $movementTypeStats = $movements->groupBy('movement_type')->map(function ($typeMovements, $type) {
            $totalMovements = $typeMovements->count();
            $totalQuantityChange = $typeMovements->sum('quantity_change');
            $totalValue = $typeMovements->sum('total_cost');
            
            return [
                'movement_type' => InventoryMovement::MOVEMENT_TYPES[$type] ?? ucfirst(str_replace('_', ' ', $type)),
                'total_movements' => $totalMovements,
                'total_quantity_change' => $totalQuantityChange,
                'total_value' => $totalValue,
                'average_quantity_per_movement' => $totalMovements > 0 ? round($totalQuantityChange / $totalMovements, 2) : 0
            ];
        })->sortByDesc('total_movements')->values();
        
        // Daily movement trends
        $dailyTrends = [];
        $period = CarbonPeriod::create($startDate, $endDate);
        
        foreach ($period as $date) {
            $dayMovements = $movements->filter(function ($movement) use ($date) {
                return $movement->movement_date->format('Y-m-d') === $date->format('Y-m-d');
            });
            
            $stockIn = $dayMovements->where('movement_type', 'stock_in')->sum('quantity_change');
            $stockOut = abs($dayMovements->where('movement_type', 'stock_out')->sum('quantity_change'));
            $rentalOut = abs($dayMovements->where('movement_type', 'rental_out')->sum('quantity_change'));
            $rentalReturn = $dayMovements->where('movement_type', 'rental_return')->sum('quantity_change');
            
            $dailyTrends[] = [
                'date' => $date->format('Y-m-d'),
                'stock_in' => $stockIn,
                'stock_out' => $stockOut,
                'rental_out' => $rentalOut,
                'rental_return' => $rentalReturn,
                'net_change' => $stockIn + $rentalReturn - $stockOut - $rentalOut,
                'total_movements' => $dayMovements->count()
            ];
        }
        
        // Top active products
        $productActivity = $movements->groupBy('inventoryItem.product.name')->map(function ($productMovements, $productName) {
            $totalMovements = $productMovements->count();
            $totalQuantityChange = $productMovements->sum('quantity_change');
            $lastMovement = $productMovements->sortByDesc('movement_date')->first();
            
            return [
                'product_name' => $productName,
                'total_movements' => $totalMovements,
                'total_quantity_change' => $totalQuantityChange,
                'last_movement_date' => $lastMovement->movement_date->format('Y-m-d'),
                'last_movement_type' => $lastMovement->movement_type
            ];
        })->sortByDesc('total_movements')->take(10)->values();
        
        // User activity analysis
        $userActivity = $movements->where('user_id', '!=', null)
            ->groupBy('user.name')->map(function ($userMovements, $userName) {
                return [
                    'user_name' => $userName,
                    'total_movements' => $userMovements->count(),
                    'movement_types' => $userMovements->pluck('movement_type')->unique()->count()
                ];
            })->sortByDesc('total_movements')->take(10)->values();
        
        return [
            'period' => [
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
                'total_days' => $startDate->diffInDays($endDate) + 1
            ],
            'summary' => [
                'total_movements' => $movements->count(),
                'total_stock_in' => $movements->where('movement_type', 'stock_in')->sum('quantity_change'),
                'total_stock_out' => abs($movements->where('movement_type', 'stock_out')->sum('quantity_change')),
                'total_rentals' => abs($movements->where('movement_type', 'rental_out')->sum('quantity_change')),
                'total_returns' => $movements->where('movement_type', 'rental_return')->sum('quantity_change'),
                'total_value_moved' => $movements->sum('total_cost'),
                'average_movements_per_day' => round($movements->count() / ($startDate->diffInDays($endDate) + 1), 1)
            ],
            'movement_type_stats' => $movementTypeStats,
            'daily_trends' => $dailyTrends,
            'top_active_products' => $productActivity,
            'user_activity' => $userActivity,
            'generated_at' => now()->format('Y-m-d H:i:s')
        ];
    }
    
    /**
     * Generate stock turnover analysis
     */
    public function getStockTurnoverReport(Carbon $startDate, Carbon $endDate): array
    {
        $products = Product::with(['inventoryItems' => function ($query) {
            $query->active();
        }])->get();
        
        $turnoverData = $products->map(function ($product) use ($startDate, $endDate) {
            $inventoryItems = $product->inventoryItems;
            $averageStock = $inventoryItems->avg('quantity_on_hand');
            
            // Calculate COGS (Cost of Goods Sold) based on rental out movements
            $rentalMovements = InventoryMovement::whereHas('inventoryItem', function ($query) use ($product) {
                $query->where('product_id', $product->id);
            })
            ->where('movement_type', 'rental_out')
            ->whereBetween('movement_date', [$startDate, $endDate])
            ->get();
            
            $totalRented = abs($rentalMovements->sum('quantity_change'));
            $averageItemCost = $inventoryItems->avg('purchase_cost') ?? 0;
            $cogs = $totalRented * $averageItemCost;
            
            $turnoverRatio = $averageStock > 0 ? $cogs / ($averageStock * $averageItemCost) : 0;
            $daysSalesInventory = $turnoverRatio > 0 ? ($startDate->diffInDays($endDate) + 1) / $turnoverRatio : 0;
            
            return [
                'product_id' => $product->id,
                'product_name' => $product->name,
                'average_stock' => round($averageStock, 2),
                'total_rented' => $totalRented,
                'cogs' => $cogs,
                'turnover_ratio' => round($turnoverRatio, 3),
                'days_sales_inventory' => round($daysSalesInventory, 1),
                'performance_rating' => $this->getTurnoverPerformanceRating($turnoverRatio)
            ];
        })->filter(function ($item) {
            return $item['average_stock'] > 0;
        })->sortByDesc('turnover_ratio')->values();
        
        return [
            'period' => [
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d')
            ],
            'summary' => [
                'total_products_analyzed' => $turnoverData->count(),
                'average_turnover_ratio' => round($turnoverData->avg('turnover_ratio'), 3),
                'best_performing_product' => $turnoverData->first(),
                'slowest_moving_product' => $turnoverData->last()
            ],
            'products' => $turnoverData,
            'performance_categories' => [
                'excellent' => $turnoverData->where('performance_rating', 'excellent')->count(),
                'good' => $turnoverData->where('performance_rating', 'good')->count(),
                'average' => $turnoverData->where('performance_rating', 'average')->count(),
                'poor' => $turnoverData->where('performance_rating', 'poor')->count()
            ],
            'generated_at' => now()->format('Y-m-d H:i:s')
        ];
    }
    
    /**
     * Generate ABC analysis report (80/20 rule for inventory)
     */
    public function getABCAnalysisReport(): array
    {
        // Get products with their rental activity and value
        $products = Product::with(['inventoryItems' => function ($query) {
            $query->active();
        }])->get()->map(function ($product) {
            $inventoryItems = $product->inventoryItems;
            $totalQuantity = $inventoryItems->sum('quantity_on_hand');
            $totalValue = $inventoryItems->sum(function ($item) {
                return ($item->current_value ?? $item->purchase_cost ?? 0) * $item->quantity_on_hand;
            });
            
            // Get rental frequency (last 90 days)
            $rentalActivity = InventoryMovement::whereHas('inventoryItem', function ($query) use ($product) {
                $query->where('product_id', $product->id);
            })
            ->where('movement_type', 'rental_out')
            ->where('movement_date', '>=', now()->subDays(90))
            ->count();
            
            return [
                'product_id' => $product->id,
                'product_name' => $product->name,
                'total_quantity' => $totalQuantity,
                'total_value' => $totalValue,
                'rental_activity' => $rentalActivity,
                'unit_value' => $totalQuantity > 0 ? $totalValue / $totalQuantity : 0
            ];
        })->filter(function ($item) {
            return $item['total_value'] > 0;
        });
        
        $totalValue = $products->sum('total_value');
        $sortedProducts = $products->sortByDesc('total_value')->values();
        
        // Assign ABC categories
        $runningValue = 0;
        $categorizedProducts = $sortedProducts->map(function ($product) use ($totalValue, &$runningValue) {
            $runningValue += $product['total_value'];
            $cumulativePercentage = ($runningValue / $totalValue) * 100;
            
            if ($cumulativePercentage <= 80) {
                $category = 'A';
                $categoryDescription = 'High Value - Tight Control';
            } elseif ($cumulativePercentage <= 95) {
                $category = 'B';
                $categoryDescription = 'Medium Value - Moderate Control';
            } else {
                $category = 'C';
                $categoryDescription = 'Low Value - Simple Control';
            }
            
            return array_merge($product, [
                'abc_category' => $category,
                'category_description' => $categoryDescription,
                'cumulative_value_percentage' => round($cumulativePercentage, 2),
                'value_percentage' => round(($product['total_value'] / $totalValue) * 100, 2)
            ]);
        });
        
        // Category summaries
        $categoryStats = $categorizedProducts->groupBy('abc_category')->map(function ($categoryProducts, $category) use ($totalValue) {
            $categoryValue = $categoryProducts->sum('total_value');
            $categoryCount = $categoryProducts->count();
            
            return [
                'category' => $category,
                'product_count' => $categoryCount,
                'total_value' => $categoryValue,
                'percentage_of_total_value' => round(($categoryValue / $totalValue) * 100, 2),
                'average_product_value' => round($categoryValue / $categoryCount, 2)
            ];
        });
        
        return [
            'summary' => [
                'total_products' => $categorizedProducts->count(),
                'total_inventory_value' => $totalValue,
                'analysis_date' => now()->format('Y-m-d')
            ],
            'category_stats' => $categoryStats,
            'products' => $categorizedProducts,
            'recommendations' => $this->getABCRecommendations($categoryStats),
            'generated_at' => now()->format('Y-m-d H:i:s')
        ];
    }
    
    /**
     * Get low stock impact analysis
     */
    public function getLowStockImpactReport(): array
    {
        $lowStockItems = InventoryItem::lowStock()->active()->with(['product.category'])->get();
        
        $impactAnalysis = $lowStockItems->map(function ($item) {
            // Calculate potential revenue loss
            $averageDailyRentals = InventoryMovement::where('inventory_item_id', $item->id)
                ->where('movement_type', 'rental_out')
                ->where('movement_date', '>=', now()->subDays(30))
                ->count() / 30;
            
            $dailyRevenuePotential = $averageDailyRentals * ($item->product->price_per_day ?? 0);
            $stockOutDays = max(0, $item->minimum_stock_level - $item->quantity_on_hand);
            $potentialRevenueLoss = $dailyRevenuePotential * $stockOutDays;
            
            return [
                'inventory_item_id' => $item->id,
                'sku' => $item->sku,
                'product_name' => $item->product->name,
                'current_stock' => $item->quantity_on_hand,
                'minimum_level' => $item->minimum_stock_level,
                'stock_deficit' => $stockOutDays,
                'average_daily_rentals' => round($averageDailyRentals, 2),
                'daily_revenue_potential' => $dailyRevenuePotential,
                'potential_revenue_loss' => $potentialRevenueLoss,
                'urgency_score' => $this->calculateUrgencyScore($item, $averageDailyRentals)
            ];
        })->sortByDesc('urgency_score');
        
        return [
            'summary' => [
                'total_low_stock_items' => $lowStockItems->count(),
                'total_potential_revenue_loss' => $impactAnalysis->sum('potential_revenue_loss'),
                'high_urgency_items' => $impactAnalysis->where('urgency_score', '>=', 8)->count(),
                'medium_urgency_items' => $impactAnalysis->whereBetween('urgency_score', [5, 7.99])->count(),
                'low_urgency_items' => $impactAnalysis->where('urgency_score', '<', 5)->count()
            ],
            'items' => $impactAnalysis->values(),
            'generated_at' => now()->format('Y-m-d H:i:s')
        ];
    }
    
    /**
     * Export report data to CSV format
     */
    public function exportReportToCSV(string $reportType, array $data, array $filters = []): string
    {
        $filename = storage_path("app/reports/{$reportType}_" . now()->format('Y-m-d_H-i-s') . '.csv');
        
        if (!file_exists(dirname($filename))) {
            mkdir(dirname($filename), 0755, true);
        }
        
        $file = fopen($filename, 'w');
        
        switch ($reportType) {
            case 'stock_valuation':
                $this->exportStockValuationCSV($file, $data);
                break;
            case 'movement_analytics':
                $this->exportMovementAnalyticsCSV($file, $data);
                break;
            case 'abc_analysis':
                $this->exportABCAnalysisCSV($file, $data);
                break;
            case 'stock_turnover':
                $this->exportStockTurnoverCSV($file, $data);
                break;
        }
        
        fclose($file);
        
        return $filename;
    }
    
    /**
     * Helper method to get turnover performance rating
     */
    private function getTurnoverPerformanceRating(float $turnoverRatio): string
    {
        if ($turnoverRatio >= 6) return 'excellent';
        if ($turnoverRatio >= 4) return 'good';
        if ($turnoverRatio >= 2) return 'average';
        return 'poor';
    }
    
    /**
     * Helper method to calculate urgency score for low stock items
     */
    private function calculateUrgencyScore(InventoryItem $item, float $averageDailyRentals): float
    {
        $stockDeficit = max(0, $item->minimum_stock_level - $item->quantity_on_hand);
        $demandFactor = $averageDailyRentals * 2; // Weight demand higher
        $stockFactor = $stockDeficit;
        
        return min(10, $demandFactor + $stockFactor);
    }
    
    /**
     * Get ABC analysis recommendations
     */
    private function getABCRecommendations(Collection $categoryStats): array
    {
        $recommendations = [];
        
        foreach ($categoryStats as $category => $stats) {
            switch ($category) {
                case 'A':
                    $recommendations[] = [
                        'category' => 'A',
                        'title' => 'High-Value Items',
                        'recommendations' => [
                            'Implement tight inventory controls',
                            'Daily monitoring of stock levels',
                            'Accurate demand forecasting',
                            'Quick supplier response arrangements',
                            'Consider just-in-time inventory'
                        ]
                    ];
                    break;
                case 'B':
                    $recommendations[] = [
                        'category' => 'B',
                        'title' => 'Medium-Value Items',
                        'recommendations' => [
                            'Weekly inventory reviews',
                            'Moderate safety stock levels',
                            'Regular supplier communication',
                            'Semi-automated reordering'
                        ]
                    ];
                    break;
                case 'C':
                    $recommendations[] = [
                        'category' => 'C',
                        'title' => 'Low-Value Items',
                        'recommendations' => [
                            'Simple inventory controls',
                            'Higher safety stock acceptable',
                            'Bulk purchasing for economies of scale',
                            'Monthly or quarterly reviews'
                        ]
                    ];
                    break;
            }
        }
        
        return $recommendations;
    }
    
    /**
     * Export stock valuation data to CSV
     */
    private function exportStockValuationCSV($file, array $data): void
    {
        // Write headers
        fputcsv($file, ['Category', 'Item Count', 'Total Quantity', 'Purchase Value', 'Current Value', 'Value Change', 'Change %']);
        
        // Write category breakdown data
        foreach ($data['category_breakdown'] as $category) {
            fputcsv($file, [
                $category['category_name'],
                $category['item_count'],
                $category['total_quantity'],
                number_format($category['purchase_value'], 2),
                number_format($category['current_value'], 2),
                number_format($category['value_change'], 2),
                $category['value_change_percentage'] . '%'
            ]);
        }
    }
    
    /**
     * Export movement analytics data to CSV
     */
    private function exportMovementAnalyticsCSV($file, array $data): void
    {
        // Write headers
        fputcsv($file, ['Date', 'Stock In', 'Stock Out', 'Rental Out', 'Rental Return', 'Net Change', 'Total Movements']);
        
        // Write daily trends data
        foreach ($data['daily_trends'] as $day) {
            fputcsv($file, [
                $day['date'],
                $day['stock_in'],
                $day['stock_out'],
                $day['rental_out'],
                $day['rental_return'],
                $day['net_change'],
                $day['total_movements']
            ]);
        }
    }
    
    /**
     * Export ABC analysis data to CSV
     */
    private function exportABCAnalysisCSV($file, array $data): void
    {
        // Write headers
        fputcsv($file, ['Product Name', 'ABC Category', 'Total Value', 'Value %', 'Cumulative %', 'Quantity', 'Unit Value']);
        
        // Write products data
        foreach ($data['products'] as $product) {
            fputcsv($file, [
                $product['product_name'],
                $product['abc_category'],
                number_format($product['total_value'], 2),
                $product['value_percentage'] . '%',
                $product['cumulative_value_percentage'] . '%',
                $product['total_quantity'],
                number_format($product['unit_value'], 2)
            ]);
        }
    }
    
    /**
     * Export stock turnover data to CSV
     */
    private function exportStockTurnoverCSV($file, array $data): void
    {
        // Write headers
        fputcsv($file, ['Product Name', 'Average Stock', 'Total Rented', 'Turnover Ratio', 'Days Sales Inventory', 'Performance Rating']);
        
        // Write products data
        foreach ($data['products'] as $product) {
            fputcsv($file, [
                $product['product_name'],
                $product['average_stock'],
                $product['total_rented'],
                $product['turnover_ratio'],
                $product['days_sales_inventory'],
                $product['performance_rating']
            ]);
        }
    }
}
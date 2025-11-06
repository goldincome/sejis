@extends('emails.layouts.base')

@section('title', 'Low Stock Alert')

@section('preheader')
{{ $totalItemsAffected }} inventory items require your attention
@endsection

@section('content')
<!-- Alert Header -->
<div class="alert-header" style="background: {{ $urgencyLevel === 'critical' ? '#dc3545' : ($urgencyLevel === 'urgent' ? '#fd7e14' : '#ffc107') }}; color: white; padding: 20px; text-align: center; border-radius: 8px; margin-bottom: 30px;">
    <h1 style="margin: 0; font-size: 24px; font-weight: bold;">
        @if($urgencyLevel === 'critical')
            🚨 CRITICAL INVENTORY ALERT
        @elseif($urgencyLevel === 'urgent')
            ⚠️ URGENT STOCK NOTICE
        @else
            📦 LOW STOCK NOTIFICATION
        @endif
    </h1>
    <p style="margin: 10px 0 0 0; font-size: 16px; opacity: 0.9;">
        {{ $totalItemsAffected }} items need immediate attention
    </p>
</div>

<!-- Summary Statistics -->
<div class="summary-stats" style="margin-bottom: 30px;">
    <table style="width: 100%; border-collapse: collapse;">
        <tr>
            <td style="width: 33.33%; text-align: center; padding: 15px; background: #f8f9fa; border-radius: 8px; margin-right: 5px;">
                <div style="font-size: 24px; font-weight: bold; color: #dc3545;">
                    {{ $criticalItems->count() }}
                </div>
                <div style="color: #6c757d; font-size: 14px;">Critical/Out of Stock</div>
            </td>
            <td style="width: 5px;"></td>
            <td style="width: 33.33%; text-align: center; padding: 15px; background: #f8f9fa; border-radius: 8px; margin: 0 2.5px;">
                <div style="font-size: 24px; font-weight: bold; color: #fd7e14;">
                    {{ $lowStockItems->where('quantity_on_hand', '>', 0)->count() }}
                </div>
                <div style="color: #6c757d; font-size: 14px;">Low Stock</div>
            </td>
            <td style="width: 5px;"></td>
            <td style="width: 33.33%; text-align: center; padding: 15px; background: #f8f9fa; border-radius: 8px; margin-left: 5px;">
                <div style="font-size: 24px; font-weight: bold; color: #17a2b8;">
                    {{ $totalItemsAffected }}
                </div>
                <div style="color: #6c757d; font-size: 14px;">Total Items</div>
            </td>
        </tr>
    </table>
</div>

<!-- Action Required Section -->
@if(count($actionRequired) > 0)
<div class="action-section" style="background: #fff3cd; border: 1px solid #ffeaa7; border-radius: 8px; padding: 20px; margin-bottom: 30px;">
    <h3 style="margin: 0 0 15px 0; color: #856404; font-size: 18px;">
        🎯 Actions Required
    </h3>
    @foreach($actionRequired as $action)
    <div style="margin-bottom: 15px; padding: 12px; background: white; border-radius: 6px; border-left: 4px solid {{ $action['priority'] === 'high' ? '#dc3545' : '#ffc107' }};">
        <div style="font-weight: bold; color: #495057; margin-bottom: 5px;">
            {{ $action['action'] }}
        </div>
        <div style="color: #6c757d; font-size: 14px; margin-bottom: 8px;">
            {{ $action['description'] }}
        </div>
        <a href="{{ $action['url'] }}" style="display: inline-block; background: #007bff; color: white; text-decoration: none; padding: 6px 12px; border-radius: 4px; font-size: 12px;">
            Take Action
        </a>
    </div>
    @endforeach
</div>
@endif

<!-- Critical Items (if any) -->
@if($criticalItems->count() > 0)
<div class="critical-section" style="background: #f8d7da; border: 1px solid #f5c6cb; border-radius: 8px; padding: 20px; margin-bottom: 30px;">
    <h3 style="margin: 0 0 15px 0; color: #721c24; font-size: 18px;">
        🚨 Critical Items ({{ $criticalItems->count() }})
    </h3>
    <div style="background: white; border-radius: 6px; overflow: hidden;">
        <table style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="background: #dc3545; color: white;">
                    <th style="padding: 12px; text-align: left; font-size: 14px; font-weight: 600;">SKU</th>
                    <th style="padding: 12px; text-align: left; font-size: 14px; font-weight: 600;">Product</th>
                    <th style="padding: 12px; text-align: center; font-size: 14px; font-weight: 600;">Stock</th>
                    <th style="padding: 12px; text-align: center; font-size: 14px; font-weight: 600;">Min Level</th>
                    <th style="padding: 12px; text-align: left; font-size: 14px; font-weight: 600;">Location</th>
                </tr>
            </thead>
            <tbody>
                @foreach($criticalItems->take(10) as $item)
                <tr style="border-bottom: 1px solid #e9ecef;">
                    <td style="padding: 12px; font-family: monospace; font-size: 13px; color: #495057;">{{ $item->sku }}</td>
                    <td style="padding: 12px; font-size: 14px; color: #495057;">
                        <strong>{{ $item->name }}</strong>
                        @if($item->product)
                            <br><small style="color: #6c757d;">{{ $item->product->name }}</small>
                        @endif
                    </td>
                    <td style="padding: 12px; text-align: center;">
                        <span style="background: #dc3545; color: white; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: bold;">
                            {{ $item->quantity_on_hand }}
                        </span>
                    </td>
                    <td style="padding: 12px; text-align: center; color: #6c757d; font-size: 14px;">
                        {{ $item->minimum_stock_level ?? 'Not Set' }}
                    </td>
                    <td style="padding: 12px; font-size: 14px; color: #495057;">
                        {{ $item->location ?? 'Unassigned' }}
                        @if($item->zone)
                            <br><small style="color: #6c757d;">Zone: {{ $item->zone }}</small>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @if($criticalItems->count() > 10)
    <div style="text-align: center; margin-top: 15px;">
        <a href="{{ route('admin.inventory.items', ['status' => 'out_of_stock']) }}" style="color: #721c24; text-decoration: none; font-weight: 500;">
            View all {{ $criticalItems->count() }} critical items →
        </a>
    </div>
    @endif
</div>
@endif

<!-- All Low Stock Items -->
<div class="items-section" style="background: white; border: 1px solid #dee2e6; border-radius: 8px; overflow: hidden; margin-bottom: 30px;">
    <div style="background: #f8f9fa; padding: 15px; border-bottom: 1px solid #dee2e6;">
        <h3 style="margin: 0; color: #495057; font-size: 18px;">
            📋 All Low Stock Items ({{ $lowStockItems->count() }})
        </h3>
    </div>
    
    <table style="width: 100%; border-collapse: collapse;">
        <thead>
            <tr style="background: #f8f9fa;">
                <th style="padding: 12px; text-align: left; font-size: 14px; font-weight: 600; color: #495057; border-bottom: 1px solid #dee2e6;">SKU</th>
                <th style="padding: 12px; text-align: left; font-size: 14px; font-weight: 600; color: #495057; border-bottom: 1px solid #dee2e6;">Product</th>
                <th style="padding: 12px; text-align: center; font-size: 14px; font-weight: 600; color: #495057; border-bottom: 1px solid #dee2e6;">Current</th>
                <th style="padding: 12px; text-align: center; font-size: 14px; font-weight: 600; color: #495057; border-bottom: 1px solid #dee2e6;">Reserved</th>
                <th style="padding: 12px; text-align: center; font-size: 14px; font-weight: 600; color: #495057; border-bottom: 1px solid #dee2e6;">Available</th>
                <th style="padding: 12px; text-align: center; font-size: 14px; font-weight: 600; color: #495057; border-bottom: 1px solid #dee2e6;">Min Level</th>
                <th style="padding: 12px; text-align: left; font-size: 14px; font-weight: 600; color: #495057; border-bottom: 1px solid #dee2e6;">Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($lowStockItems->take(15) as $item)
            <tr style="border-bottom: 1px solid #e9ecef;">
                <td style="padding: 12px; font-family: monospace; font-size: 13px; color: #495057;">{{ $item->sku }}</td>
                <td style="padding: 12px; font-size: 14px; color: #495057;">
                    <strong>{{ $item->name }}</strong>
                    @if($item->product)
                        <br><small style="color: #6c757d;">{{ $item->product->name }}</small>
                    @endif
                </td>
                <td style="padding: 12px; text-align: center;">
                    <span style="background: {{ $item->quantity_on_hand <= 0 ? '#dc3545' : ($item->quantity_on_hand <= ($item->minimum_stock_level * 0.5) ? '#fd7e14' : '#ffc107') }}; color: white; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: bold;">
                        {{ $item->quantity_on_hand }}
                    </span>
                </td>
                <td style="padding: 12px; text-align: center; color: #6c757d; font-size: 14px;">
                    {{ $item->quantity_reserved ?? 0 }}
                </td>
                <td style="padding: 12px; text-align: center;">
                    <span style="color: {{ $item->getAvailableQuantity() > 0 ? '#28a745' : '#dc3545' }}; font-weight: bold; font-size: 14px;">
                        {{ $item->getAvailableQuantity() }}
                    </span>
                </td>
                <td style="padding: 12px; text-align: center; color: #6c757d; font-size: 14px;">
                    {{ $item->minimum_stock_level ?? 'Not Set' }}
                </td>
                <td style="padding: 12px; font-size: 12px;">
                    @if($item->quantity_on_hand <= 0)
                        <span style="background: #dc3545; color: white; padding: 2px 6px; border-radius: 3px;">OUT OF STOCK</span>
                    @elseif($item->minimum_stock_level && $item->quantity_on_hand <= $item->minimum_stock_level * 0.25)
                        <span style="background: #dc3545; color: white; padding: 2px 6px; border-radius: 3px;">CRITICAL</span>
                    @elseif($item->minimum_stock_level && $item->quantity_on_hand <= $item->minimum_stock_level * 0.5)
                        <span style="background: #fd7e14; color: white; padding: 2px 6px; border-radius: 3px;">URGENT</span>
                    @else
                        <span style="background: #ffc107; color: #495057; padding: 2px 6px; border-radius: 3px;">LOW</span>
                    @endif
                    
                    @if($item->isMaintenanceDue())
                        <br><span style="background: #6f42c1; color: white; padding: 2px 6px; border-radius: 3px; margin-top: 2px; display: inline-block;">MAINTENANCE DUE</span>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    
    @if($lowStockItems->count() > 15)
    <div style="text-align: center; padding: 15px; background: #f8f9fa; border-top: 1px solid #dee2e6;">
        <a href="{{ $itemsUrl }}" style="color: #007bff; text-decoration: none; font-weight: 500;">
            View all {{ $lowStockItems->count() }} low stock items →
        </a>
    </div>
    @endif
</div>

<!-- Quick Actions -->
<div class="quick-actions" style="background: #e3f2fd; border: 1px solid #bbdefb; border-radius: 8px; padding: 20px; margin-bottom: 30px;">
    <h3 style="margin: 0 0 15px 0; color: #0277bd; font-size: 18px;">
        ⚡ Quick Actions
    </h3>
    <div style="display: flex; flex-wrap: wrap; gap: 10px;">
        <a href="{{ $dashboardUrl }}" style="display: inline-block; background: #007bff; color: white; text-decoration: none; padding: 10px 16px; border-radius: 6px; font-size: 14px; font-weight: 500;">
            📊 View Dashboard
        </a>
        <a href="{{ route('admin.inventory.items', ['status' => 'low_stock']) }}" style="display: inline-block; background: #ffc107; color: #495057; text-decoration: none; padding: 10px 16px; border-radius: 6px; font-size: 14px; font-weight: 500;">
            📦 Manage Low Stock
        </a>
        <a href="{{ route('admin.inventory.create') }}" style="display: inline-block; background: #28a745; color: white; text-decoration: none; padding: 10px 16px; border-radius: 6px; font-size: 14px; font-weight: 500;">
            ➕ Add Inventory
        </a>
        @if($criticalItems->count() > 0)
        <a href="{{ route('admin.inventory.items', ['status' => 'out_of_stock']) }}" style="display: inline-block; background: #dc3545; color: white; text-decoration: none; padding: 10px 16px; border-radius: 6px; font-size: 14px; font-weight: 500;">
            🚨 Handle Critical Items
        </a>
        @endif
    </div>
</div>

<!-- Alert Settings Info -->
<div class="settings-info" style="background: #f8f9fa; border-radius: 8px; padding: 15px; margin-bottom: 20px;">
    <div style="color: #6c757d; font-size: 13px; line-height: 1.5;">
        <strong>Alert Settings:</strong><br>
        • Frequency: {{ ucfirst($alertSettings['notification_frequency'] ?? 'daily') }}<br>
        • Threshold Type: {{ ucfirst(str_replace('_', ' ', $alertSettings['threshold_type'] ?? 'below_minimum')) }}<br>
        • Alert Level: {{ ucfirst($alertSettings['alert_level'] ?? 'warning') }}
    </div>
</div>

<!-- Footer Note -->
<div style="text-align: center; color: #6c757d; font-size: 13px; border-top: 1px solid #dee2e6; padding-top: 20px; margin-top: 30px;">
    <p style="margin: 0;">
        This is an automated inventory alert from {{ $companySettings['company_name'] }}.<br>
        Generated on {{ now()->format('M j, Y \a\t g:i A T') }}
    </p>
    @if(setting('disable_low_stock_alerts') === false)
    <p style="margin: 10px 0 0 0;">
        <a href="{{ route('admin.inventory.index') }}" style="color: #007bff; text-decoration: none;">
            Manage alert settings
        </a>
    </p>
    @endif
</div>
@endsection

@section('styles')
<style>
    @media only screen and (max-width: 600px) {
        .summary-stats table td {
            display: block !important;
            width: 100% !important;
            margin-bottom: 10px !important;
        }
        
        .quick-actions div {
            display: block !important;
        }
        
        .quick-actions a {
            display: block !important;
            margin-bottom: 8px !important;
            text-align: center !important;
        }
        
        table th, table td {
            font-size: 12px !important;
            padding: 8px !important;
        }
    }
</style>
@endsection
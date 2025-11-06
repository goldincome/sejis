@extends('layouts.admin')

@section('title', 'Inventory Management')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0">Inventory Management</h1>
                    <p class="text-muted">Monitor stock levels, alerts, and inventory movements</p>
                </div>
                <div>
                    <a href="{{ route('admin.inventory.items.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add Inventory Item
                    </a>
                    <button type="button" class="btn btn-outline-primary" onclick="sendLowStockAlerts()">
                        <i class="fas fa-envelope"></i> Send Alerts
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Stock Value
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                £{{ number_format($stats['inventory_summary']['total_stock_value'], 2) }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-pound-sign fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Total Items
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($stats['inventory_summary']['total_items']) }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-boxes fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Low Stock Items
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $stats['inventory_summary']['low_stock_count'] }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                Out of Stock
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $stats['inventory_summary']['out_of_stock_count'] }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-times-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Daily Activity -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Today's Activity</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 text-center">
                            <div class="h4 text-success">{{ $stats['daily_activity']['stock_in'] }}</div>
                            <div class="text-muted">Stock In</div>
                        </div>
                        <div class="col-md-3 text-center">
                            <div class="h4 text-danger">{{ $stats['daily_activity']['stock_out'] }}</div>
                            <div class="text-muted">Stock Out</div>
                        </div>
                        <div class="col-md-3 text-center">
                            <div class="h4 text-warning">{{ $stats['daily_activity']['rentals'] }}</div>
                            <div class="text-muted">Rentals</div>
                        </div>
                        <div class="col-md-3 text-center">
                            <div class="h4 text-info">{{ $stats['daily_activity']['total_movements'] }}</div>
                            <div class="text-muted">Total Movements</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Low Stock Alerts -->
    @if(count($lowStockAlerts) > 0)
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow border-left-warning">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-warning">Low Stock Alerts ({{ count($lowStockAlerts) }})</h6>
                    <a href="{{ route('admin.inventory.items', ['status' => 'low_stock']) }}" class="btn btn-sm btn-outline-warning">
                        View All
                    </a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>SKU</th>
                                    <th>Product</th>
                                    <th>Location</th>
                                    <th>Current Stock</th>
                                    <th>Minimum Level</th>
                                    <th>Alert Level</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach(array_slice($lowStockAlerts, 0, 5) as $alert)
                                <tr>
                                    <td>
                                        <a href="{{ route('admin.inventory.items.show', $alert['inventory_item_id']) }}">
                                            {{ $alert['sku'] }}
                                        </a>
                                    </td>
                                    <td>{{ $alert['product_name'] }}</td>
                                    <td>{{ $alert['location'] }}</td>
                                    <td>
                                        <span class="badge badge-{{ $alert['current_stock'] == 0 ? 'danger' : 'warning' }}">
                                            {{ $alert['current_stock'] }}
                                        </span>
                                    </td>
                                    <td>{{ $alert['minimum_level'] }}</td>
                                    <td>
                                        @switch($alert['alert_level'])
                                            @case('critical')
                                                <span class="badge badge-danger">Critical</span>
                                                @break
                                            @case('warning') 
                                                <span class="badge badge-warning">Warning</span>
                                                @break
                                            @default
                                                <span class="badge badge-info">Low</span>
                                        @endswitch
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-primary" onclick="adjustStock({{ $alert['inventory_item_id'] }})">
                                            <i class="fas fa-plus"></i> Add Stock
                                        </button>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Alerts and Maintenance -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">System Alerts</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-6 text-center">
                            <div class="h5 text-warning">{{ $stats['alerts']['maintenance_due'] }}</div>
                            <div class="text-muted small">Maintenance Due</div>
                        </div>
                        <div class="col-6 text-center">
                            <div class="h5 text-info">{{ $stats['alerts']['warranty_expiring'] }}</div>
                            <div class="text-muted small">Warranty Expiring</div>
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-6 text-center">
                            <div class="h5 text-secondary">{{ $stats['alerts']['requires_cleaning'] }}</div>
                            <div class="text-muted small">Needs Cleaning</div>
                        </div>
                        <div class="col-6 text-center">
                            <div class="h5 text-secondary">{{ $stats['alerts']['requires_inspection'] }}</div>
                            <div class="text-muted small">Needs Inspection</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Stock Health</h6>
                </div>
                <div class="card-body">
                    <div class="progress mb-3">
                        <div class="progress-bar bg-success" role="progressbar" 
                             style="width: {{ $stats['inventory_summary']['stock_health_percentage'] }}%">
                            {{ $stats['inventory_summary']['stock_health_percentage'] }}%
                        </div>
                    </div>
                    <div class="text-center">
                        <small class="text-muted">
                            Stock health based on items above minimum levels
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Movements -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Recent Movements</h6>
                    <a href="{{ route('admin.inventory.movements') }}" class="btn btn-sm btn-outline-primary">
                        View All Movements
                    </a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Item</th>
                                    <th>Type</th>
                                    <th>Quantity Change</th>
                                    <th>User</th>
                                    <th>Reason</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentMovements as $movement)
                                <tr>
                                    <td>{{ $movement->movement_date->format('M d, H:i') }}</td>
                                    <td>
                                        <a href="{{ route('admin.inventory.items.show', $movement->inventoryItem->id) }}">
                                            {{ $movement->inventoryItem->sku }}
                                        </a>
                                        <br>
                                        <small class="text-muted">{{ $movement->inventoryItem->product->name }}</small>
                                    </td>
                                    <td>
                                        <span class="badge badge-{{ $movement->quantity_change > 0 ? 'success' : ($movement->quantity_change < 0 ? 'danger' : 'secondary') }}">
                                            {{ $movement->movement_type_label }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="text-{{ $movement->quantity_change > 0 ? 'success' : ($movement->quantity_change < 0 ? 'danger' : 'muted') }}">
                                            {{ $movement->quantity_change > 0 ? '+' : '' }}{{ $movement->quantity_change }}
                                        </span>
                                    </td>
                                    <td>{{ $movement->user->name ?? 'System' }}</td>
                                    <td>{{ Str::limit($movement->reason, 50) }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted">No recent movements</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Stock Adjustment Modal -->
<div class="modal fade" id="stockAdjustmentModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Adjust Stock</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="stockAdjustmentForm">
                <div class="modal-body">
                    @csrf
                    <input type="hidden" id="inventory_item_id" name="inventory_item_id">
                    
                    <div class="form-group">
                        <label for="quantity_change">Quantity Change</label>
                        <input type="number" class="form-control" id="quantity_change" name="quantity_change" required>
                        <small class="form-text text-muted">Enter positive number to add stock, negative to remove</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="reason">Reason</label>
                        <input type="text" class="form-control" id="reason" name="reason" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Adjust Stock</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function adjustStock(inventoryItemId) {
    $('#inventory_item_id').val(inventoryItemId);
    $('#stockAdjustmentModal').modal('show');
}

function sendLowStockAlerts() {
    if (confirm('Send low stock alert emails to administrators?')) {
        fetch('{{ route("admin.inventory.send-alerts") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Alerts Sent!',
                    text: data.message,
                    timer: 3000,
                    showConfirmButton: false
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: data.message
                });
            }
        })
        .catch(error => {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Failed to send alerts'
            });
        });
    }
}

$('#stockAdjustmentForm').on('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const inventoryItemId = $('#inventory_item_id').val();
    
    fetch(`/admin/inventory/${inventoryItemId}/adjust-stock`, {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            $('#stockAdjustmentModal').modal('hide');
            Swal.fire({
                icon: 'success',
                title: 'Stock Adjusted!',
                text: data.message,
                timer: 3000,
                showConfirmButton: false
            }).then(() => {
                location.reload();
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: data.message
            });
        }
    })
    .catch(error => {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Failed to adjust stock'
        });
    });
});
</script>
@endpush
@extends('layouts.admin')

@section('title', 'Inventory Item Details')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('admin.inventory.index') }}">Inventory</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('admin.inventory.items') }}">Items</a></li>
                            <li class="breadcrumb-item active">{{ $item->sku }}</li>
                        </ol>
                    </nav>
                    <h1 class="h3 mb-0">{{ $item->name }}</h1>
                    <p class="text-muted">SKU: {{ $item->sku }}</p>
                </div>
                <div>
                    <a href="{{ route('admin.inventory.edit', $item->id) }}" class="btn btn-secondary">
                        <i class="fas fa-edit"></i> Edit Item
                    </a>
                    <button type="button" class="btn btn-success" onclick="quickAdjustStock()">
                        <i class="fas fa-plus-minus"></i> Adjust Stock
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Left Column - Item Details -->
        <div class="col-lg-8">
            <!-- Basic Information -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Item Information</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Product:</strong></td>
                                    <td>
                                        @if($item->product)
                                            <a href="{{ route('admin.products.show', $item->product->id) }}">
                                                {{ $item->product->name }}
                                            </a>
                                        @else
                                            <span class="text-muted">No product linked</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>SKU:</strong></td>
                                    <td><code>{{ $item->sku }}</code></td>
                                </tr>
                                <tr>
                                    <td><strong>Name:</strong></td>
                                    <td>{{ $item->name }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Description:</strong></td>
                                    <td>{{ $item->description ?: 'No description' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Location:</strong></td>
                                    <td>
                                        {{ $item->location ?: 'Unassigned' }}
                                        @if($item->zone)
                                            <span class="badge badge-secondary">{{ $item->zone }}</span>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Condition:</strong></td>
                                    <td>
                                        <span class="badge badge-{{ $item->getConditionColor() }} badge-lg">
                                            {{ ucfirst($item->condition) }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Purchase Cost:</strong></td>
                                    <td>
                                        @if($item->purchase_cost)
                                            £{{ number_format($item->purchase_cost, 2) }}
                                        @else
                                            <span class="text-muted">Not specified</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Purchase Date:</strong></td>
                                    <td>
                                        @if($item->purchase_date)
                                            {{ $item->purchase_date->format('M j, Y') }}
                                        @else
                                            <span class="text-muted">Not specified</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Supplier:</strong></td>
                                    <td>{{ $item->supplier ?: 'Not specified' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Created:</strong></td>
                                    <td>{{ $item->created_at->format('M j, Y g:i A') }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Stock Information -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Stock Information</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="text-center">
                                <h2 class="text-{{ $item->getStockStatusColor() }}">{{ $item->quantity_on_hand }}</h2>
                                <p class="text-muted mb-0">On Hand</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <h2 class="text-info">{{ $item->quantity_reserved }}</h2>
                                <p class="text-muted mb-0">Reserved</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <h2 class="text-{{ $item->getAvailableQuantity() > 0 ? 'success' : 'danger' }}">
                                    {{ $item->getAvailableQuantity() }}
                                </h2>
                                <p class="text-muted mb-0">Available</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <h2 class="text-warning">{{ $item->minimum_stock_level ?: 'N/A' }}</h2>
                                <p class="text-muted mb-0">Low Stock Alert</p>
                            </div>
                        </div>
                    </div>
                    
                    @if($item->isLowStock())
                        <div class="alert alert-warning mt-3">
                            <i class="fas fa-exclamation-triangle"></i>
                            <strong>Low Stock Alert:</strong> This item is below the minimum threshold.
                        </div>
                    @endif
                    
                    @if($item->isOutOfStock())
                        <div class="alert alert-danger mt-3">
                            <i class="fas fa-times-circle"></i>
                            <strong>Out of Stock:</strong> This item is currently out of stock.
                        </div>
                    @endif
                </div>
            </div>

            <!-- Maintenance & Warranty -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Maintenance & Warranty</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Maintenance Schedule</h6>
                            <table class="table table-sm">
                                <tr>
                                    <td><strong>Last Maintenance:</strong></td>
                                    <td>
                                        @if($item->last_maintenance_date)
                                            {{ $item->last_maintenance_date->format('M j, Y') }}
                                        @else
                                            <span class="text-muted">Never</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Next Maintenance:</strong></td>
                                    <td>
                                        @if($item->next_maintenance_date)
                                            {{ $item->next_maintenance_date->format('M j, Y') }}
                                            @if($item->isMaintenanceDue())
                                                <span class="badge badge-warning">Due</span>
                                            @elseif($item->isMaintenanceOverdue())
                                                <span class="badge badge-danger">Overdue</span>
                                            @endif
                                        @else
                                            <span class="text-muted">Not scheduled</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Maintenance Notes:</strong></td>
                                    <td>{{ $item->maintenance_notes ?: 'No notes' }}</td>
                                </tr>
                            </table>
                            
                            @if($item->isMaintenanceDue() || $item->isMaintenanceOverdue())
                                <button type="button" class="btn btn-warning btn-sm" onclick="scheduleMaintenance()">
                                    <i class="fas fa-wrench"></i> Schedule Maintenance
                                </button>
                            @endif
                        </div>
                        <div class="col-md-6">
                            <h6>Warranty Information</h6>
                            <table class="table table-sm">
                                <tr>
                                    <td><strong>Warranty Start:</strong></td>
                                    <td>
                                        @if($item->warranty_start_date)
                                            {{ $item->warranty_start_date->format('M j, Y') }}
                                        @else
                                            <span class="text-muted">Not specified</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Warranty End:</strong></td>
                                    <td>
                                        @if($item->warranty_end_date)
                                            {{ $item->warranty_end_date->format('M j, Y') }}
                                            @if($item->isWarrantyExpired())
                                                <span class="badge badge-danger">Expired</span>
                                            @elseif($item->isWarrantyExpiring())
                                                <span class="badge badge-warning">Expiring Soon</span>
                                            @else
                                                <span class="badge badge-success">Active</span>
                                            @endif
                                        @else
                                            <span class="text-muted">Not specified</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Warranty Provider:</strong></td>
                                    <td>{{ $item->warranty_provider ?: 'Not specified' }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Movements -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Recent Stock Movements</h6>
                </div>
                <div class="card-body">
                    @if($recentMovements->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Type</th>
                                        <th>Quantity</th>
                                        <th>From</th>
                                        <th>To</th>
                                        <th>Reason</th>
                                        <th>User</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recentMovements as $movement)
                                        <tr>
                                            <td>{{ $movement->created_at->format('M j, Y g:i A') }}</td>
                                            <td>
                                                <span class="badge badge-{{ $movement->getTypeColor() }}">
                                                    {{ ucfirst(str_replace('_', ' ', $movement->movement_type)) }}
                                                </span>
                                            </td>
                                            <td>
                                                @if($movement->quantity_change > 0)
                                                    <span class="text-success">+{{ $movement->quantity_change }}</span>
                                                @else
                                                    <span class="text-danger">{{ $movement->quantity_change }}</span>
                                                @endif
                                            </td>
                                            <td>{{ $movement->from_location ?: '-' }}</td>
                                            <td>{{ $movement->to_location ?: '-' }}</td>
                                            <td>{{ $movement->reason ?: '-' }}</td>
                                            <td>
                                                @if($movement->user)
                                                    {{ $movement->user->name }}
                                                @else
                                                    <span class="text-muted">System</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="text-center">
                            <a href="{{ route('admin.inventory.movements', ['item_id' => $item->id]) }}" class="btn btn-outline-primary btn-sm">
                                View All Movements
                            </a>
                        </div>
                    @else
                        <div class="text-center text-muted py-3">
                            <i class="fas fa-history fa-2x mb-2"></i>
                            <p>No stock movements recorded yet.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Right Column - Quick Actions & Status -->
        <div class="col-lg-4">
            <!-- Quick Actions -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Quick Actions</h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <button type="button" class="btn btn-success" onclick="quickAdjustStock()">
                            <i class="fas fa-plus-minus"></i> Adjust Stock Level
                        </button>
                        <button type="button" class="btn btn-info" onclick="transferItem()">
                            <i class="fas fa-exchange-alt"></i> Transfer Location
                        </button>
                        <button type="button" class="btn btn-warning" onclick="updateCondition()">
                            <i class="fas fa-tools"></i> Update Condition
                        </button>
                        @if($item->isMaintenanceDue() || $item->isMaintenanceOverdue())
                            <button type="button" class="btn btn-warning" onclick="scheduleMaintenance()">
                                <i class="fas fa-wrench"></i> Schedule Maintenance
                            </button>
                        @endif
                        <hr>
                        <a href="{{ route('admin.inventory.edit', $item->id) }}" class="btn btn-outline-secondary">
                            <i class="fas fa-edit"></i> Edit Details
                        </a>
                        <button type="button" class="btn btn-outline-danger" onclick="deleteItem()">
                            <i class="fas fa-trash"></i> Delete Item
                        </button>
                    </div>
                </div>
            </div>

            <!-- Status Alerts -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Status Alerts</h6>
                </div>
                <div class="card-body">
                    @php
                        $alerts = [];
                        if ($item->isLowStock()) {
                            $alerts[] = ['type' => 'warning', 'icon' => 'exclamation-triangle', 'message' => 'Stock level is below minimum threshold'];
                        }
                        if ($item->isOutOfStock()) {
                            $alerts[] = ['type' => 'danger', 'icon' => 'times-circle', 'message' => 'Item is out of stock'];
                        }
                        if ($item->isMaintenanceDue()) {
                            $alerts[] = ['type' => 'warning', 'icon' => 'wrench', 'message' => 'Maintenance is due'];
                        }
                        if ($item->isMaintenanceOverdue()) {
                            $alerts[] = ['type' => 'danger', 'icon' => 'wrench', 'message' => 'Maintenance is overdue'];
                        }
                        if ($item->isWarrantyExpiring()) {
                            $alerts[] = ['type' => 'warning', 'icon' => 'shield-alt', 'message' => 'Warranty expires soon'];
                        }
                        if ($item->isWarrantyExpired()) {
                            $alerts[] = ['type' => 'danger', 'icon' => 'shield-alt', 'message' => 'Warranty has expired'];
                        }
                        if ($item->condition === 'poor') {
                            $alerts[] = ['type' => 'danger', 'icon' => 'tools', 'message' => 'Item condition is poor'];
                        }
                    @endphp

                    @if(count($alerts) > 0)
                        @foreach($alerts as $alert)
                            <div class="alert alert-{{ $alert['type'] }} alert-dismissible fade show">
                                <i class="fas fa-{{ $alert['icon'] }}"></i>
                                {{ $alert['message'] }}
                            </div>
                        @endforeach
                    @else
                        <div class="text-center text-muted">
                            <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
                            <p>No status alerts</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Stock History Chart -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Stock History (30 Days)</h6>
                </div>
                <div class="card-body">
                    <canvas id="stockHistoryChart" width="400" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Stock Adjustment Modal -->
<div class="modal fade" id="stockAdjustModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Adjust Stock Level</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="stockAdjustForm">
                <div class="modal-body">
                    @csrf
                    <input type="hidden" name="item_id" value="{{ $item->id }}">
                    
                    <div class="alert alert-info">
                        <strong>Current Stock:</strong> {{ $item->quantity_on_hand }} units
                    </div>
                    
                    <div class="form-group">
                        <label for="adjustment_type">Adjustment Type</label>
                        <select class="form-control" id="adjustment_type" name="adjustment_type" required>
                            <option value="add">Add Stock</option>
                            <option value="subtract">Remove Stock</option>
                            <option value="set">Set Exact Amount</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="quantity">Quantity</label>
                        <input type="number" class="form-control" id="quantity" name="quantity" 
                               min="0" step="1" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="reason">Reason</label>
                        <select class="form-control" id="reason" name="reason" required>
                            <option value="purchase">Purchase/Received</option>
                            <option value="damage">Damaged Item</option>
                            <option value="theft">Theft/Loss</option>
                            <option value="returned">Customer Return</option>
                            <option value="maintenance">Maintenance</option>
                            <option value="transfer">Transfer</option>
                            <option value="correction">Inventory Correction</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="notes">Notes</label>
                        <textarea class="form-control" id="notes" name="notes" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Apply Adjustment</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
$(document).ready(function() {
    // Initialize stock history chart
    initStockHistoryChart();
    
    // Stock adjustment form
    $('#stockAdjustForm').on('submit', function(e) {
        e.preventDefault();
        submitStockAdjustment();
    });
});

function quickAdjustStock() {
    $('#stockAdjustModal').modal('show');
}

function transferItem() {
    // Implement transfer functionality
    Swal.fire({
        title: 'Transfer Item',
        text: 'Transfer functionality will be implemented here',
        icon: 'info'
    });
}

function updateCondition() {
    // Implement condition update functionality
    Swal.fire({
        title: 'Update Condition',
        text: 'Condition update functionality will be implemented here',
        icon: 'info'
    });
}

function scheduleMaintenance() {
    // Implement maintenance scheduling
    Swal.fire({
        title: 'Schedule Maintenance',
        text: 'Maintenance scheduling functionality will be implemented here',
        icon: 'info'
    });
}

function deleteItem() {
    Swal.fire({
        title: 'Delete Item?',
        text: 'This action cannot be undone. All movement history will be preserved.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            // Implement delete functionality
            console.log('Delete item {{ $item->id }}');
        }
    });
}

function submitStockAdjustment() {
    const formData = new FormData(document.getElementById('stockAdjustForm'));
    const submitBtn = document.querySelector('#stockAdjustForm button[type="submit"]');
    
    submitBtn.innerHTML = 'Processing...';
    submitBtn.disabled = true;
    
    fetch('{{ route("admin.inventory.adjust-stock") }}', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: 'Stock Adjusted!',
                text: data.message,
                timer: 2000,
                showConfirmButton: false
            });
            $('#stockAdjustModal').modal('hide');
            window.location.reload();
        } else {
            throw new Error(data.message || 'Failed to adjust stock');
        }
    })
    .catch(error => {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: error.message || 'Failed to adjust stock'
        });
    })
    .finally(() => {
        submitBtn.innerHTML = 'Apply Adjustment';
        submitBtn.disabled = false;
    });
}

function initStockHistoryChart() {
    const ctx = document.getElementById('stockHistoryChart').getContext('2d');
    
    // Sample data - in real implementation, this would come from the controller
    const chartData = {
        labels: @json($stockHistory['dates'] ?? []),
        datasets: [{
            label: 'Stock Level',
            data: @json($stockHistory['quantities'] ?? []),
            borderColor: 'rgb(75, 192, 192)',
            backgroundColor: 'rgba(75, 192, 192, 0.2)',
            tension: 0.1
        }]
    };
    
    new Chart(ctx, {
        type: 'line',
        data: chartData,
        options: {
            responsive: true,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
}
</script>
@endpush
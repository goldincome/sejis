@extends('layouts.admin')

@section('title', 'Inventory Movements')

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
                            <li class="breadcrumb-item active">Movements</li>
                        </ol>
                    </nav>
                    <h1 class="h3 mb-0">Inventory Movements</h1>
                    <p class="text-muted">Track all stock movements and changes</p>
                </div>
                <div>
                    <button type="button" class="btn btn-outline-primary" onclick="exportMovements()">
                        <i class="fas fa-download"></i> Export Report
                    </button>
                    <button type="button" class="btn btn-primary" onclick="addManualMovement()">
                        <i class="fas fa-plus"></i> Add Manual Movement
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Today's Movements
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $stats['today_movements'] ?? 0 }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-exchange-alt fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Stock Added (Today)
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                +{{ $stats['today_stock_in'] ?? 0 }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-arrow-up fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Stock Removed (Today)
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                -{{ $stats['today_stock_out'] ?? 0 }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-arrow-down fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Active Rentals
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $stats['active_rentals'] ?? 0 }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clock fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" id="filterForm">
                <div class="row">
                    <div class="col-md-2">
                        <label for="item_id">Item</label>
                        <select class="form-control" id="item_id" name="item_id">
                            <option value="">All Items</option>
                            @foreach($inventoryItems as $item)
                                <option value="{{ $item->id }}" {{ request('item_id') == $item->id ? 'selected' : '' }}>
                                    {{ $item->sku }} - {{ $item->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="movement_type">Movement Type</label>
                        <select class="form-control" id="movement_type" name="movement_type">
                            <option value="">All Types</option>
                            <option value="purchase" {{ request('movement_type') == 'purchase' ? 'selected' : '' }}>Purchase</option>
                            <option value="sale" {{ request('movement_type') == 'sale' ? 'selected' : '' }}>Sale</option>
                            <option value="rental_out" {{ request('movement_type') == 'rental_out' ? 'selected' : '' }}>Rental Out</option>
                            <option value="rental_return" {{ request('movement_type') == 'rental_return' ? 'selected' : '' }}>Rental Return</option>
                            <option value="damage" {{ request('movement_type') == 'damage' ? 'selected' : '' }}>Damage</option>
                            <option value="theft" {{ request('movement_type') == 'theft' ? 'selected' : '' }}>Theft</option>
                            <option value="transfer" {{ request('movement_type') == 'transfer' ? 'selected' : '' }}>Transfer</option>
                            <option value="maintenance" {{ request('movement_type') == 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                            <option value="adjustment" {{ request('movement_type') == 'adjustment' ? 'selected' : '' }}>Manual Adjustment</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="date_from">Date From</label>
                        <input type="date" class="form-control" id="date_from" name="date_from" 
                               value="{{ request('date_from') }}">
                    </div>
                    <div class="col-md-2">
                        <label for="date_to">Date To</label>
                        <input type="date" class="form-control" id="date_to" name="date_to" 
                               value="{{ request('date_to') }}">
                    </div>
                    <div class="col-md-2">
                        <label for="user_id">User</label>
                        <select class="form-control" id="user_id" name="user_id">
                            <option value="">All Users</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                    {{ $user->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label>&nbsp;</label>
                        <div class="d-flex">
                            <button type="submit" class="btn btn-primary mr-2">
                                <i class="fas fa-search"></i> Filter
                            </button>
                            <a href="{{ route('admin.inventory.movements') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-times"></i> Clear
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Movements Table -->
    <div class="card shadow">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                Movement History 
                <span class="badge badge-secondary">{{ $movements->total() }} records</span>
            </h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="thead-light">
                        <tr>
                            <th>
                                <a href="{{ request()->fullUrlWithQuery(['sort' => 'created_at', 'direction' => request('direction') == 'asc' ? 'desc' : 'asc']) }}">
                                    Date/Time
                                    @if(request('sort') == 'created_at')
                                        <i class="fas fa-sort-{{ request('direction') == 'asc' ? 'up' : 'down' }}"></i>
                                    @endif
                                </a>
                            </th>
                            <th>Item</th>
                            <th>Movement Type</th>
                            <th>Quantity Change</th>
                            <th>From Location</th>
                            <th>To Location</th>
                            <th>Reason</th>
                            <th>User</th>
                            <th>Order/Reference</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($movements as $movement)
                            <tr>
                                <td>
                                    <div>
                                        <strong>{{ $movement->created_at->format('M j, Y') }}</strong>
                                        <br><small class="text-muted">{{ $movement->created_at->format('g:i A') }}</small>
                                    </div>
                                </td>
                                <td>
                                    @if($movement->inventoryItem)
                                        <div>
                                            <strong>{{ $movement->inventoryItem->sku }}</strong>
                                            <br><small class="text-muted">{{ $movement->inventoryItem->name }}</small>
                                        </div>
                                    @else
                                        <span class="text-muted">Item deleted</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge badge-{{ $movement->getTypeColor() }}">
                                        {{ ucfirst(str_replace('_', ' ', $movement->movement_type)) }}
                                    </span>
                                </td>
                                <td>
                                    @if($movement->quantity_change > 0)
                                        <span class="text-success font-weight-bold">
                                            <i class="fas fa-arrow-up"></i> +{{ $movement->quantity_change }}
                                        </span>
                                    @elseif($movement->quantity_change < 0)
                                        <span class="text-danger font-weight-bold">
                                            <i class="fas fa-arrow-down"></i> {{ $movement->quantity_change }}
                                        </span>
                                    @else
                                        <span class="text-muted">0</span>
                                    @endif
                                </td>
                                <td>
                                    @if($movement->from_location)
                                        <div>
                                            <strong>{{ $movement->from_location }}</strong>
                                            @if($movement->from_zone)
                                                <br><small class="text-muted">{{ $movement->from_zone }}</small>
                                            @endif
                                        </div>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($movement->to_location)
                                        <div>
                                            <strong>{{ $movement->to_location }}</strong>
                                            @if($movement->to_zone)
                                                <br><small class="text-muted">{{ $movement->to_zone }}</small>
                                            @endif
                                        </div>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="text-sm">{{ $movement->reason ?: 'No reason specified' }}</span>
                                </td>
                                <td>
                                    @if($movement->user)
                                        <div>
                                            <strong>{{ $movement->user->name }}</strong>
                                            <br><small class="text-muted">{{ $movement->user->email }}</small>
                                        </div>
                                    @else
                                        <span class="badge badge-secondary">System</span>
                                    @endif
                                </td>
                                <td>
                                    @if($movement->order_id)
                                        <a href="{{ route('admin.orders.show', $movement->order_id) }}" 
                                           class="btn btn-sm btn-outline-primary">
                                            Order #{{ $movement->order_id }}
                                        </a>
                                    @elseif($movement->reference_id)
                                        <span class="text-muted">Ref: {{ $movement->reference_id }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <button type="button" class="btn btn-sm btn-outline-info" 
                                                onclick="viewMovementDetails({{ $movement->id }})" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        @if($movement->canBeReversed())
                                            <button type="button" class="btn btn-sm btn-outline-warning" 
                                                    onclick="reverseMovement({{ $movement->id }})" title="Reverse Movement">
                                                <i class="fas fa-undo"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center py-4">
                                    <div class="text-muted">
                                        <i class="fas fa-history fa-3x mb-3"></i>
                                        <p>No movements found matching your criteria.</p>
                                        <button type="button" class="btn btn-primary" onclick="addManualMovement()">
                                            <i class="fas fa-plus"></i> Add First Movement
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($movements->hasPages())
                <div class="d-flex justify-content-center mt-4">
                    {{ $movements->appends(request()->query())->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Movement Details Modal -->
<div class="modal fade" id="movementDetailsModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Movement Details</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body" id="movementDetailsContent">
                <!-- Details will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Manual Movement Modal -->
<div class="modal fade" id="manualMovementModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Manual Movement</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="manualMovementForm">
                <div class="modal-body">
                    @csrf
                    
                    <div class="form-group">
                        <label for="manual_item_id">Inventory Item *</label>
                        <select class="form-control" id="manual_item_id" name="item_id" required>
                            <option value="">Select item...</option>
                            @foreach($inventoryItems as $item)
                                <option value="{{ $item->id }}">{{ $item->sku }} - {{ $item->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="manual_movement_type">Movement Type *</label>
                        <select class="form-control" id="manual_movement_type" name="movement_type" required>
                            <option value="adjustment">Manual Adjustment</option>
                            <option value="purchase">Purchase/Received</option>
                            <option value="damage">Damage/Loss</option>
                            <option value="theft">Theft</option>
                            <option value="transfer">Transfer</option>
                            <option value="maintenance">Maintenance</option>
                            <option value="correction">Inventory Correction</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="manual_quantity_change">Quantity Change *</label>
                        <input type="number" class="form-control" id="manual_quantity_change" 
                               name="quantity_change" step="1" required 
                               placeholder="Use negative numbers to remove stock">
                        <small class="form-text text-muted">
                            Positive numbers add stock, negative numbers remove stock
                        </small>
                    </div>
                    
                    <div class="form-group">
                        <label for="manual_reason">Reason *</label>
                        <input type="text" class="form-control" id="manual_reason" name="reason" 
                               placeholder="Brief description of the movement" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="manual_from_location">From Location</label>
                        <input type="text" class="form-control" id="manual_from_location" name="from_location">
                    </div>
                    
                    <div class="form-group">
                        <label for="manual_to_location">To Location</label>
                        <input type="text" class="form-control" id="manual_to_location" name="to_location">
                    </div>
                    
                    <div class="form-group">
                        <label for="manual_cost">Cost (£)</label>
                        <input type="number" class="form-control" id="manual_cost" name="cost" 
                               step="0.01" min="0" placeholder="0.00">
                    </div>
                    
                    <div class="form-group">
                        <label for="manual_supplier">Supplier</label>
                        <input type="text" class="form-control" id="manual_supplier" name="supplier">
                    </div>
                    
                    <div class="form-group">
                        <label for="manual_notes">Additional Notes</label>
                        <textarea class="form-control" id="manual_notes" name="notes" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Record Movement
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.border-left-primary { border-left: 0.25rem solid #4e73df !important; }
.border-left-success { border-left: 0.25rem solid #1cc88a !important; }
.border-left-warning { border-left: 0.25rem solid #f6c23e !important; }
.border-left-info { border-left: 0.25rem solid #36b9cc !important; }

.text-sm { font-size: 0.875rem; }

.font-weight-bold { font-weight: 700 !important; }
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    // Manual movement form submission
    $('#manualMovementForm').on('submit', function(e) {
        e.preventDefault();
        submitManualMovement();
    });
});

function addManualMovement() {
    $('#manualMovementForm')[0].reset();
    $('#manualMovementModal').modal('show');
}

function viewMovementDetails(movementId) {
    // Show loading state
    $('#movementDetailsContent').html('<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Loading...</div>');
    $('#movementDetailsModal').modal('show');
    
    // Fetch movement details
    fetch(`/admin/inventory/movements/${movementId}/details`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                $('#movementDetailsContent').html(data.html);
            } else {
                throw new Error(data.message || 'Failed to load movement details');
            }
        })
        .catch(error => {
            $('#movementDetailsContent').html(`
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle"></i>
                    Failed to load movement details: ${error.message}
                </div>
            `);
        });
}

function reverseMovement(movementId) {
    Swal.fire({
        title: 'Reverse Movement?',
        text: 'This will create a new movement that reverses the selected movement. This action cannot be undone.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#f6c23e',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, reverse it!'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch(`/admin/inventory/movements/${movementId}/reverse`, {
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
                        title: 'Movement Reversed!',
                        text: data.message,
                        timer: 2000,
                        showConfirmButton: false
                    });
                    window.location.reload();
                } else {
                    throw new Error(data.message || 'Failed to reverse movement');
                }
            })
            .catch(error => {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: error.message || 'Failed to reverse movement'
                });
            });
        }
    });
}

function submitManualMovement() {
    const formData = new FormData(document.getElementById('manualMovementForm'));
    const submitBtn = document.querySelector('#manualMovementForm button[type="submit"]');
    
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Recording...';
    submitBtn.disabled = true;
    
    fetch('{{ route("admin.inventory.manual-movement") }}', {
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
                title: 'Movement Recorded!',
                text: data.message,
                timer: 2000,
                showConfirmButton: false
            });
            $('#manualMovementModal').modal('hide');
            window.location.reload();
        } else {
            throw new Error(data.message || 'Failed to record movement');
        }
    })
    .catch(error => {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: error.message || 'Failed to record movement'
        });
    })
    .finally(() => {
        submitBtn.innerHTML = '<i class="fas fa-save"></i> Record Movement';
        submitBtn.disabled = false;
    });
}

function exportMovements() {
    const params = new URLSearchParams(window.location.search);
    window.location.href = '{{ route("admin.inventory.export-movements") }}?' + params.toString();
}
</script>
@endpush
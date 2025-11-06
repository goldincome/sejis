@extends('layouts.admin')

@section('title', 'Inventory Items')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0">Inventory Items</h1>
                    <p class="text-muted">Manage stock levels, locations, and item conditions</p>
                </div>
                <div>
                    <a href="{{ route('admin.inventory.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add New Item
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" id="filterForm">
                <div class="row">
                    <div class="col-md-3">
                        <label for="search">Search Items</label>
                        <input type="text" class="form-control" id="search" name="search" 
                               value="{{ request('search') }}" placeholder="Search by name, SKU, or description...">
                    </div>
                    <div class="col-md-2">
                        <label for="product_id">Product</label>
                        <select class="form-control" id="product_id" name="product_id">
                            <option value="">All Products</option>
                            @foreach($products as $product)
                                <option value="{{ $product->id }}" {{ request('product_id') == $product->id ? 'selected' : '' }}>
                                    {{ $product->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="condition">Condition</label>
                        <select class="form-control" id="condition" name="condition">
                            <option value="">All Conditions</option>
                            <option value="excellent" {{ request('condition') == 'excellent' ? 'selected' : '' }}>Excellent</option>
                            <option value="good" {{ request('condition') == 'good' ? 'selected' : '' }}>Good</option>
                            <option value="fair" {{ request('condition') == 'fair' ? 'selected' : '' }}>Fair</option>
                            <option value="poor" {{ request('condition') == 'poor' ? 'selected' : '' }}>Poor</option>
                            <option value="maintenance" {{ request('condition') == 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="location">Location</label>
                        <input type="text" class="form-control" id="location" name="location" 
                               value="{{ request('location') }}" placeholder="Location...">
                    </div>
                    <div class="col-md-2">
                        <label for="stock_status">Stock Status</label>
                        <select class="form-control" id="stock_status" name="stock_status">
                            <option value="">All Stock</option>
                            <option value="in_stock" {{ request('stock_status') == 'in_stock' ? 'selected' : '' }}>In Stock</option>
                            <option value="low_stock" {{ request('stock_status') == 'low_stock' ? 'selected' : '' }}>Low Stock</option>
                            <option value="out_of_stock" {{ request('stock_status') == 'out_of_stock' ? 'selected' : '' }}>Out of Stock</option>
                            <option value="reserved" {{ request('stock_status') == 'reserved' ? 'selected' : '' }}>Has Reservations</option>
                        </select>
                    </div>
                    <div class="col-md-1">
                        <label>&nbsp;</label>
                        <button type="submit" class="btn btn-primary form-control">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="btn-group" role="group">
                <button type="button" class="btn btn-outline-primary btn-sm" onclick="exportInventory()">
                    <i class="fas fa-download"></i> Export CSV
                </button>
                <button type="button" class="btn btn-outline-success btn-sm" onclick="bulkAdjustStock()">
                    <i class="fas fa-edit"></i> Bulk Adjust
                </button>
                <button type="button" class="btn btn-outline-warning btn-sm" onclick="generateLowStockReport()">
                    <i class="fas fa-exclamation-triangle"></i> Low Stock Report
                </button>
            </div>
        </div>
    </div>

    <!-- Inventory Items Table -->
    <div class="card shadow">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                Inventory Items 
                <span class="badge badge-secondary">{{ $inventoryItems->total() }} items</span>
            </h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="inventoryTable">
                    <thead class="thead-light">
                        <tr>
                            <th>
                                <input type="checkbox" id="selectAll">
                            </th>
                            <th>
                                <a href="{{ request()->fullUrlWithQuery(['sort' => 'sku', 'direction' => request('direction') == 'asc' ? 'desc' : 'asc']) }}">
                                    SKU 
                                    @if(request('sort') == 'sku')
                                        <i class="fas fa-sort-{{ request('direction') == 'asc' ? 'up' : 'down' }}"></i>
                                    @endif
                                </a>
                            </th>
                            <th>Product</th>
                            <th>
                                <a href="{{ request()->fullUrlWithQuery(['sort' => 'quantity_on_hand', 'direction' => request('direction') == 'asc' ? 'desc' : 'asc']) }}">
                                    Stock Level
                                    @if(request('sort') == 'quantity_on_hand')
                                        <i class="fas fa-sort-{{ request('direction') == 'asc' ? 'up' : 'down' }}"></i>
                                    @endif
                                </a>
                            </th>
                            <th>Reserved</th>
                            <th>Available</th>
                            <th>Location</th>
                            <th>Condition</th>
                            <th>
                                <a href="{{ request()->fullUrlWithQuery(['sort' => 'updated_at', 'direction' => request('direction') == 'asc' ? 'desc' : 'asc']) }}">
                                    Last Updated
                                    @if(request('sort') == 'updated_at')
                                        <i class="fas fa-sort-{{ request('direction') == 'asc' ? 'up' : 'down' }}"></i>
                                    @endif
                                </a>
                            </th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($inventoryItems as $item)
                            <tr class="{{ $item->isLowStock() ? 'table-warning' : '' }}">
                                <td>
                                    <input type="checkbox" class="item-checkbox" value="{{ $item->id }}">
                                </td>
                                <td>
                                    <strong>{{ $item->sku }}</strong>
                                    @if($item->isLowStock())
                                        <span class="badge badge-warning badge-sm ml-1">Low Stock</span>
                                    @endif
                                    @if($item->isOutOfStock())
                                        <span class="badge badge-danger badge-sm ml-1">Out of Stock</span>
                                    @endif
                                </td>
                                <td>
                                    <div>
                                        <strong>{{ $item->product->name ?? 'N/A' }}</strong>
                                        @if($item->name !== $item->product->name)
                                            <br><small class="text-muted">{{ $item->name }}</small>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <span class="badge badge-{{ $item->getStockStatusColor() }} badge-lg">
                                        {{ $item->quantity_on_hand }}
                                    </span>
                                    @if($item->minimum_stock_level)
                                        <br><small class="text-muted">Min: {{ $item->minimum_stock_level }}</small>
                                    @endif
                                </td>
                                <td>
                                    @if($item->quantity_reserved > 0)
                                        <span class="badge badge-info">{{ $item->quantity_reserved }}</span>
                                    @else
                                        <span class="text-muted">0</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge badge-{{ $item->getAvailableQuantity() > 0 ? 'success' : 'secondary' }}">
                                        {{ $item->getAvailableQuantity() }}
                                    </span>
                                </td>
                                <td>
                                    <div>
                                        <strong>{{ $item->location ?? 'Unassigned' }}</strong>
                                        @if($item->zone)
                                            <br><small class="text-muted">Zone: {{ $item->zone }}</small>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <span class="badge badge-{{ $item->getConditionColor() }}">
                                        {{ ucfirst($item->condition) }}
                                    </span>
                                    @if($item->isMaintenanceDue())
                                        <br><span class="badge badge-warning badge-sm">Maintenance Due</span>
                                    @endif
                                </td>
                                <td>
                                    <small class="text-muted">
                                        {{ $item->updated_at->format('M j, Y') }}<br>
                                        {{ $item->updated_at->format('g:i A') }}
                                    </small>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('admin.inventory.show', $item->id) }}" 
                                           class="btn btn-sm btn-outline-primary" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.inventory.edit', $item->id) }}" 
                                           class="btn btn-sm btn-outline-secondary" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button type="button" class="btn btn-sm btn-outline-success" 
                                                onclick="quickAdjustStock({{ $item->id }}, '{{ $item->sku }}')" title="Adjust Stock">
                                            <i class="fas fa-plus-minus"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-warning" 
                                                onclick="transferItem({{ $item->id }}, '{{ $item->sku }}')" title="Transfer">
                                            <i class="fas fa-exchange-alt"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center py-4">
                                    <div class="text-muted">
                                        <i class="fas fa-box-open fa-3x mb-3"></i>
                                        <p>No inventory items found matching your criteria.</p>
                                        <a href="{{ route('admin.inventory.create') }}" class="btn btn-primary">
                                            <i class="fas fa-plus"></i> Add First Item
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($inventoryItems->hasPages())
                <div class="d-flex justify-content-center mt-4">
                    {{ $inventoryItems->appends(request()->query())->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Quick Stock Adjustment Modal -->
<div class="modal fade" id="quickAdjustModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Quick Stock Adjustment</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="quickAdjustForm">
                <div class="modal-body">
                    @csrf
                    <input type="hidden" id="adjust_item_id" name="item_id">
                    
                    <div class="form-group">
                        <label>Item SKU</label>
                        <input type="text" class="form-control" id="adjust_item_sku" readonly>
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
                        <label for="notes">Notes (Optional)</label>
                        <textarea class="form-control" id="notes" name="notes" rows="3" 
                                  placeholder="Additional details about this adjustment..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Apply Adjustment
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Transfer Item Modal -->
<div class="modal fade" id="transferModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Transfer Item</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="transferForm">
                <div class="modal-body">
                    @csrf
                    <input type="hidden" id="transfer_item_id" name="item_id">
                    
                    <div class="form-group">
                        <label>Item SKU</label>
                        <input type="text" class="form-control" id="transfer_item_sku" readonly>
                    </div>
                    
                    <div class="form-group">
                        <label for="new_location">New Location</label>
                        <input type="text" class="form-control" id="new_location" name="new_location" 
                               placeholder="e.g., Warehouse A, Kitchen Bay 3" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="new_zone">New Zone (Optional)</label>
                        <input type="text" class="form-control" id="new_zone" name="new_zone" 
                               placeholder="e.g., A1, B2, C3">
                    </div>
                    
                    <div class="form-group">
                        <label for="transfer_notes">Transfer Notes</label>
                        <textarea class="form-control" id="transfer_notes" name="notes" rows="3" 
                                  placeholder="Reason for transfer and any additional notes..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-exchange-alt"></i> Transfer Item
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.table-warning {
    background-color: #fff3cd !important;
}

.badge-lg {
    font-size: 0.875rem;
    padding: 0.375rem 0.75rem;
}

.btn-group .btn {
    border-radius: 0.25rem !important;
    margin-right: 2px;
}

.item-checkbox {
    cursor: pointer;
}

#selectAll {
    cursor: pointer;
}
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    // Auto-submit filter form on change
    $('#filterForm select, #filterForm input').on('change keyup', debounce(function() {
        if ($(this).attr('type') !== 'text' || $(this).val().length > 2 || $(this).val().length === 0) {
            $('#filterForm').submit();
        }
    }, 300));
    
    // Select all functionality
    $('#selectAll').on('change', function() {
        $('.item-checkbox').prop('checked', this.checked);
    });
    
    // Quick adjust form submission
    $('#quickAdjustForm').on('submit', function(e) {
        e.preventDefault();
        submitQuickAdjust();
    });
    
    // Transfer form submission
    $('#transferForm').on('submit', function(e) {
        e.preventDefault();
        submitTransfer();
    });
});

function quickAdjustStock(itemId, itemSku) {
    $('#adjust_item_id').val(itemId);
    $('#adjust_item_sku').val(itemSku);
    $('#quickAdjustForm')[0].reset();
    $('#adjust_item_id').val(itemId);
    $('#quickAdjustModal').modal('show');
}

function transferItem(itemId, itemSku) {
    $('#transfer_item_id').val(itemId);
    $('#transfer_item_sku').val(itemSku);
    $('#transferForm')[0].reset();
    $('#transfer_item_id').val(itemId);
    $('#transferModal').modal('show');
}

function submitQuickAdjust() {
    const formData = new FormData(document.getElementById('quickAdjustForm'));
    const submitBtn = document.querySelector('#quickAdjustForm button[type="submit"]');
    
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
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
            $('#quickAdjustModal').modal('hide');
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
        submitBtn.innerHTML = '<i class="fas fa-save"></i> Apply Adjustment';
        submitBtn.disabled = false;
    });
}

function submitTransfer() {
    const formData = new FormData(document.getElementById('transferForm'));
    const submitBtn = document.querySelector('#transferForm button[type="submit"]');
    
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Transferring...';
    submitBtn.disabled = true;
    
    fetch('{{ route("admin.inventory.transfer") }}', {
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
                title: 'Item Transferred!',
                text: data.message,
                timer: 2000,
                showConfirmButton: false
            });
            $('#transferModal').modal('hide');
            window.location.reload();
        } else {
            throw new Error(data.message || 'Failed to transfer item');
        }
    })
    .catch(error => {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: error.message || 'Failed to transfer item'
        });
    })
    .finally(() => {
        submitBtn.innerHTML = '<i class="fas fa-exchange-alt"></i> Transfer Item';
        submitBtn.disabled = false;
    });
}

function exportInventory() {
    window.location.href = '{{ route("admin.inventory.export") }}?' + new URLSearchParams(window.location.search);
}

function bulkAdjustStock() {
    const selected = $('.item-checkbox:checked').map(function() {
        return this.value;
    }).get();
    
    if (selected.length === 0) {
        Swal.fire({
            icon: 'warning',
            title: 'No Items Selected',
            text: 'Please select items to perform bulk adjustment'
        });
        return;
    }
    
    // Implement bulk adjustment functionality
    console.log('Bulk adjust for items:', selected);
}

function generateLowStockReport() {
    window.open('{{ route("admin.inventory.low-stock-report") }}', '_blank');
}

function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}
</script>
@endpush
@extends('layouts.user')
@section('title', 'My Orders')
@section('page_title', 'My Orders')
@section('page_intro', 'Track and manage all your kitchen rental orders.')

@push('styles')
<style>
.filter-card {
    background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
}
.order-card {
    transition: all 0.3s ease;
    border-left: 4px solid transparent;
}
.order-card:hover {
    transform: translateX(5px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.1);
}
.order-card.status-pending { border-left-color: #f59e0b; }
.order-card.status-paid { border-left-color: #3b82f6; }
.order-card.status-confirmed { border-left-color: #10b981; }
.order-card.status-completed { border-left-color: #059669; }
.order-card.status-cancelled { border-left-color: #ef4444; }
.order-card.status-failed { border-left-color: #dc2626; }
</style>
@endpush

@section('content')
<div class="lg:col-span-3">
    <div class="space-y-6">
        
        <!-- Filters Section -->
        <div class="filter-card p-6 rounded-lg shadow-md">
            <form method="GET" action="{{ route('user.orders') }}" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <!-- Status Filter -->
                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                        <select name="status" id="status" class="w-full rounded-md border-gray-300 shadow-sm focus:border-accent focus:ring-accent">
                            <option value="">All Statuses</option>
                            @foreach($statuses as $status)
                            <option value="{{ $status->value }}" {{ request('status') === $status->value ? 'selected' : '' }}>
                                {{ ucfirst($status->value) }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Date From -->
                    <div>
                        <label for="date_from" class="block text-sm font-medium text-gray-700 mb-1">From Date</label>
                        <input type="date" name="date_from" id="date_from" value="{{ request('date_from') }}" 
                               class="w-full rounded-md border-gray-300 shadow-sm focus:border-accent focus:ring-accent">
                    </div>

                    <!-- Date To -->
                    <div>
                        <label for="date_to" class="block text-sm font-medium text-gray-700 mb-1">To Date</label>
                        <input type="date" name="date_to" id="date_to" value="{{ request('date_to') }}" 
                               class="w-full rounded-md border-gray-300 shadow-sm focus:border-accent focus:ring-accent">
                    </div>

                    <!-- Search -->
                    <div>
                        <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                        <input type="text" name="search" id="search" value="{{ request('search') }}" 
                               placeholder="Order ID, Product name..."
                               class="w-full rounded-md border-gray-300 shadow-sm focus:border-accent focus:ring-accent">
                    </div>
                </div>

                <div class="flex flex-wrap gap-3 items-center">
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-accent text-white text-sm font-medium rounded-md hover:bg-accent-dark transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                        Filter
                    </button>
                    
                    <a href="{{ route('user.orders') }}" class="inline-flex items-center px-4 py-2 bg-gray-300 text-gray-700 text-sm font-medium rounded-md hover:bg-gray-400 transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                        Clear
                    </a>

                    <!-- Results Count -->
                    <div class="text-sm text-gray-600 ml-auto">
                        Showing {{ $orders->firstItem() ?? 0 }} to {{ $orders->lastItem() ?? 0 }} 
                        of {{ $orders->total() }} results
                    </div>
                </div>
            </form>
        </div>

        <!-- Orders Section -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-xl font-semibold text-gray-900">Order History</h2>
            </div>

            @if($orders->count() > 0)
                <!-- Desktop View -->
                <div class="hidden md:block overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Items</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($orders as $order)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">#{{ $order->order_no }}</div>
                                    <div class="text-sm text-gray-500">Order #{{ $order->order_no }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-900">
                                        {{ $order->orderDetails->count() }} item(s)
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        @foreach($order->orderDetails->take(2) as $detail)
                                            {{ $detail->name }}@if(!$loop->last), @endif
                                        @endforeach
                                        @if($order->orderDetails->count() > 2)
                                            <span class="text-gray-400">... +{{ $order->orderDetails->count() - 2 }} more</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <div>{{ $order->created_at->format('M d, Y') }}</div>
                                    <div class="text-xs">{{ $order->created_at->format('h:i A') }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">£{{ number_format($order->total, 2) }}</div>
                                    @if($order->payment_gateway)
                                    <div class="text-xs text-gray-500">via {{ ucfirst($order->payment_gateway) }}</div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @include('front.dashboard.partials.order-status-badge', ['status' => $order->status->value])
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm space-x-2">
                                    <a href="{{ route('user.order.details', $order->order_no) }}" 
                                       class="text-accent hover:underline font-medium">View</a>
                                    
                                    @if(in_array($order->status, ['paid', 'completed', 'delivered']))
                                        <a href="{{ route('user.order.invoice', $order->order_no) }}" 
                                           class="text-blue-600 hover:underline">Invoice</a>
                                    @endif
                                    
                                    @can('cancel', $order)
                                        <button onclick="cancelOrder('{{ $order->order_no }}')" 
                                                class="text-red-600 hover:underline">Cancel</button>
                                    @endcan
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Mobile View -->
                <div class="md:hidden divide-y divide-gray-200">
                    @foreach($orders as $order)
                    <div class="order-card status-{{ $order->status }} p-4">
                        <div class="flex justify-between items-start mb-3">
                            <div>
                                <h3 class="font-medium text-gray-900">#{{ $order->order_no }}</h3>
                                <p class="text-sm text-gray-500">{{ $order->created_at->format('M d, Y h:i A') }}</p>
                            </div>
                            @include('front.dashboard.partials.order-status-badge', ['status' => $order->status->value])
                        </div>
                        
                        <div class="mb-3">
                            <p class="text-sm text-gray-600">{{ $order->orderDetails->count() }} item(s)</p>
                            <p class="text-lg font-semibold text-gray-900">£{{ number_format($order->total, 2) }}</p>
                        </div>
                        
                        <div class="flex flex-wrap gap-2">
                            <a href="{{ route('user.order.details', $order->order_no) }}" 
                               class="inline-flex items-center px-3 py-1 bg-gray-100 text-gray-700 text-sm rounded-md hover:bg-gray-200 transition-colors">
                                View Details
                            </a>
                            
                            @if(in_array($order->status, ['paid', 'completed', 'delivered']))
                                <a href="{{ route('user.order.invoice', $order->order_no) }}" 
                                   class="inline-flex items-center px-3 py-1 bg-blue-100 text-blue-700 text-sm rounded-md hover:bg-blue-200 transition-colors">
                                    Invoice
                                </a>
                            @endif
                            
                            @can('cancel', $order)
                                <button onclick="cancelOrder('{{ $order->order_no }}')" 
                                        class="inline-flex items-center px-3 py-1 bg-red-100 text-red-700 text-sm rounded-md hover:bg-red-200 transition-colors">
                                    Cancel
                                </button>
                            @endcan
                        </div>
                    </div>
                    @endforeach
                </div>

                <!-- Pagination -->
                <div class="px-6 py-4 border-t border-gray-200">
                    {{ $orders->withQueryString()->links() }}
                </div>

            @else
                <!-- Empty State -->
                <div class="text-center py-12">
                    <div class="text-gray-400 text-6xl mb-4">📦</div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">
                        @if(request()->hasAny(['status', 'date_from', 'date_to', 'search']))
                            No orders found matching your filters
                        @else
                            No orders yet
                        @endif
                    </h3>
                    <p class="text-gray-600 mb-6">
                        @if(request()->hasAny(['status', 'date_from', 'date_to', 'search']))
                            Try adjusting your search criteria or clearing the filters.
                        @else
                            Start by booking your first kitchen rental to see your orders here.
                        @endif
                    </p>
                    
                    @if(request()->hasAny(['status', 'date_from', 'date_to', 'search']))
                        <a href="{{ route('user.orders') }}" 
                           class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 transition-colors mr-3">
                            Clear Filters
                        </a>
                    @endif
                    
                    <a href="{{ route('kitchen-rentals.index') }}" 
                       class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-accent hover:bg-accent-dark transition-colors">
                        Browse Kitchens
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Cancel Order Modal -->
<div id="cancelOrderModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Cancel Order</h3>
            <form id="cancelOrderForm">
                <div class="mb-4">
                    <label for="cancelReason" class="block text-sm font-medium text-gray-700 mb-2">
                        Reason for cancellation (optional)
                    </label>
                    <textarea id="cancelReason" name="reason" rows="3" 
                              class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-accent focus:border-transparent"
                              placeholder="Please tell us why you're cancelling..."></textarea>
                </div>
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="closeCancelModal()" 
                            class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400 transition-colors">
                        Keep Order
                    </button>
                    <button type="submit" 
                            class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition-colors">
                        Cancel Order
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
let currentOrderReference = null;

function cancelOrder(orderReference) {
    currentOrderReference = orderReference;
    document.getElementById('cancelOrderModal').classList.remove('hidden');
}

function closeCancelModal() {
    document.getElementById('cancelOrderModal').classList.add('hidden');
    currentOrderReference = null;
    document.getElementById('cancelOrderForm').reset();
}

document.getElementById('cancelOrderForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    if (!currentOrderReference) return;
    
    const reason = document.getElementById('cancelReason').value;
    const submitBtn = e.target.querySelector('button[type="submit"]');
    const originalText = submitBtn.textContent;
    
    submitBtn.disabled = true;
    submitBtn.textContent = 'Cancelling...';
    
    try {
        const response = await fetch(`/user/orders/${currentOrderReference}/cancel`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ reason })
        });
        
        const data = await response.json();
        
        if (data.success) {
            // Show success message and reload page
            alert('Order cancelled successfully');
            window.location.reload();
        } else {
            alert(data.message || 'Unable to cancel order');
        }
    } catch (error) {
        alert('An error occurred. Please try again.');
    } finally {
        submitBtn.disabled = false;
        submitBtn.textContent = originalText;
        closeCancelModal();
    }
});

// Close modal when clicking outside
document.getElementById('cancelOrderModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeCancelModal();
    }
});
</script>
@endpush
@endsection
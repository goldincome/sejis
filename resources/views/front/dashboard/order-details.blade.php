@extends('layouts.user')
@section('title', 'Order Details - #' . $order->order_no)
@section('page_title', 'Order Details')
@section('page_intro', 'Order #' . $order->order_no)

@php
    use App\Enums\ProductTypeEnum; // Import the Enum to use in the view
@endphp

@push('styles')
<style>
.timeline-item {
    position: relative;
    padding-left: 2rem;
}
.timeline-item::before {
    content: '';
    position: absolute;
    left: 0.5rem;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #e5e7eb;
}
.timeline-item:last-child::before {
    background: linear-gradient(to bottom, #e5e7eb 0%, #e5e7eb 2rem, transparent 2rem);
}
.timeline-dot {
    position: absolute;
    left: 0;
    top: 0.375rem;
    width: 1rem;
    height: 1rem;
    border-radius: 50%;
    border: 2px solid #fff;
    z-index: 1;
}
.timeline-dot.completed {
    background: #10b981;
}
.timeline-dot.current {
    background: #3b82f6;
    animation: pulse 2s infinite;
}
.timeline-dot.pending {
    background: #9ca3af;
}
@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.5; }
}
.action-button {
    transition: all 0.3s ease;
}
.action-button:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.1);
}
</style>
@endpush

@section('content')
<div class="lg:col-span-3">
    <div class="space-y-6">
        
        <!-- Back Button -->
        <div>
            <a href="{{ route('user.orders') }}" class="inline-flex items-center text-accent hover:underline">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
                Back to Orders
            </a>
        </div>

        <!-- Order Header -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="px-6 py-4 bg-gradient-to-r from-blue-50 to-indigo-50 border-b border-gray-200">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Order #{{ $order->order_no }}</h1>
                        <p class="text-sm text-gray-600 mt-1">
                            Placed on {{ $order->created_at->format('F d, Y \a\t h:i A') }}
                        </p>
                    </div>
                    <div class="mt-4 md:mt-0 flex items-center space-x-4">
                        @include('front.dashboard.partials.order-status-badge', ['status' => $order->status->value])
                        <div class="text-right">
                            <div class="text-2xl font-bold text-gray-900">£{{ number_format($order->total, 2) }}</div>
                            @if($order->payment_gateway)
                            <div class="text-sm text-gray-500">via {{ ucfirst($order->payment_gateway) }}</div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                <div class="flex flex-wrap gap-3">
                    @if(in_array($order->status->value, ['paid', 'completed', 'delivered']))
                        <a href="{{ route('user.order.invoice', $order->order_no) }}" 
                           class="action-button inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            Download Invoice
                        </a>
                    @endif

                    @can('cancel', $order)
                        <button onclick="cancelOrder()" 
                                class="action-button inline-flex items-center px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-md hover:bg-red-700">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                            Cancel Order
                        </button>
                    @endcan

                    @can('requestRefund', $order)
                        <button onclick="requestRefund()" 
                                class="action-button inline-flex items-center px-4 py-2 bg-orange-600 text-white text-sm font-medium rounded-md hover:bg-orange-700">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 15v-1a4 4 0 00-4-4H8m0 0l3 3m-3-3l3-3m9 14V5a2 2 0 00-2-2H6a2 2 0 00-2 2v16l4-2 4 2 4-2 4 2z"></path>
                            </svg>
                            Request Refund
                        </button>
                    @endcan

                    <button onclick="contactSupport()" 
                            class="action-button inline-flex items-center px-4 py-2 bg-gray-600 text-white text-sm font-medium rounded-md hover:bg-gray-700">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                        </svg>
                        Contact Support
                    </button>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            <!-- Order Timeline -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-6">Order Timeline</h2>
                    <div class="space-y-6">
                        @foreach($timeline as $item)
                        <div class="timeline-item">
                            <div class="timeline-dot {{ $item['completed'] ? 'completed' : 'pending' }}"></div>
                            <div class="pl-4">
                                <h3 class="text-sm font-medium text-gray-900">{{ $item['title'] }}</h3>
                                <p class="text-sm text-gray-600 mt-1">{{ $item['description'] }}</p>
                                @if($item['date'])
                                <p class="text-xs text-gray-500 mt-1">{{ $item['date']->format('M d, Y h:i A') }}</p>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Order Details -->
            <div class="lg:col-span-2 space-y-6">
                
                <!-- Order Items -->
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-900">Order Items</h2>
                    </div>
                    <div class="divide-y divide-gray-200">
                        @foreach($order->orderDetails as $detail)
                        <div class="px-6 py-4">
                            <div class="flex items-start space-x-4">
                                @if($detail->product && $detail->product->image)
                                <div class="flex-shrink-0">
                                    <img src="{{ asset('storage/' . $detail->product->image) }}" 
                                         alt="{{ $detail->name }}" 
                                         class="w-16 h-16 object-cover rounded-lg">
                                </div>
                                @endif
                                <div class="flex-1">
                                    <h3 class="text-sm font-medium text-gray-900">{{ $detail->name }}</h3>
                                    <div class="mt-1 space-y-1 text-sm text-gray-600">

                                        {{-- START: DYNAMIC CONTENT --}}
                                        @if($detail->product_type == ProductTypeEnum::ITEM_RENTAL->value)
                                            <!-- EQUIPMENT RENTAL DETAILS -->
                                            <div>Price: £{{ number_format($detail->price, 2) }} per unit (for duration)</div>
                                            <div>Quantity: {{ $detail->quantity }} unit(s)</div>
                                            <div class="flex items-center">
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                                </svg>
                                                Start Date: {{ \Carbon\Carbon::parse($detail->start_date)->format('M d, Y') }}
                                            </div>
                                            <div class="flex items-center">
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                                </svg>
                                                End Date: {{ \Carbon\Carbon::parse($detail->end_date)->format('M d, Y') }}
                                            </div>
                                            @php
                                                $startDate = \Carbon\Carbon::parse($detail->start_date);
                                                $endDate = \Carbon\Carbon::parse($detail->end_date);
                                                $durationInDays = $startDate->diffInDays($endDate) + 1;
                                            @endphp
                                            <div class="flex items-center">
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                </svg>
                                                Duration: {{ $durationInDays }} day(s)
                                            </div>

                                        @else
                                            <!-- KITCHEN RENTAL DETAILS (and fallback) -->
                                            <div>Price: £{{ number_format($detail->price, 2) }} per hour</div>
                                            <div>Quantity: {{ $detail->quantity }} hour(s)</div>
                                            @if($detail->booked_date)
                                            <div class="flex items-center">
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                                </svg>
                                                Booking Date: {{ \Carbon\Carbon::parse($detail->booked_date)->format('M d, Y') }}
                                            </div>
                                            @endif
                                            @if($detail->booked_durations)
                                                @php
                                                    $duration = json_decode($detail->booked_durations, true);
                                                @endphp
                                                @if(isset($duration['time_slot']))
                                                <div class="flex items-center">
                                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                    </svg>
                                                    Time: {{ $duration['time_slot'] }}
                                                </div>
                                                @endif
                                            @endif
                                        @endif
                                        {{-- END: DYNAMIC CONTENT --}}

                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="text-sm font-medium text-gray-900">
                                        £{{ number_format($detail->sub_total, 2) }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

                <!-- Order Summary -->
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-900">Order Summary</h2>
                    </div>
                    <div class="px-6 py-4 space-y-3">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Subtotal</span>
                            <span class="text-gray-900">£{{ number_format($order->sub_total, 2) }}</span>
                        </div>
                        @if($order->tax > 0)
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Tax</span>
                            <span class="text-gray-900">£{{ number_format($order->tax, 2) }}</span>
                        </div>
                        @endif
                        <div class="border-t border-gray-200 pt-3">
                            <div class="flex justify-between text-lg font-semibold">
                                <span class="text-gray-900">Total</span>
                                <span class="text-gray-900">£{{ number_format($order->total, 2) }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Payment Information -->
                @if($order->payment_gateway)
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-900">Payment Information</h2>
                    </div>
                    <div class="px-6 py-4 space-y-3">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Payment Method</span>
                            <span class="text-gray-900">{{ ucfirst($order->payment_gateway) }}</span>
                        </div>
                        @if($order->transaction_id)
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Transaction ID</span>
                            <span class="text-gray-900 font-mono">{{ $order->transaction_id }}</span>
                        </div>
                        @endif
                        @if($order->paid_at)
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Payment Date</span>
                            <span class="text-gray-900">{{ $order->paid_at->format('M d, Y h:i A') }}</span>
                        </div>
                        @endif
                        @if($order->bankDeposit)
                        <div class="bg-blue-50 p-4 rounded-md">
                            <h4 class="text-sm font-medium text-blue-900 mb-2">Bank Deposit Details</h4>
                            <div class="space-y-1 text-sm text-blue-800">
                                <div>Bank Reference: {{ $order->bankDeposit->bank_reference }}</div>
                                <div>Deposit Date: {{ $order->bankDeposit->deposit_date->format('M d, Y') }}</div>
                                @if($order->bankDeposit->verified_by)
                                <div>Verified by: {{ $order->bankDeposit->verified_by }}</div>
                                @endif
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
                @endif

            </div>
        </div>
    </div>
</div>

<!-- Cancel Order Modal -->
<div id="cancelOrderModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Cancel Order</h3>
            <p class="text-sm text-gray-600 mb-4">Are you sure you want to cancel this order? This action cannot be undone.</p>
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

<!-- Refund Request Modal -->
<div id="refundRequestModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Request Refund</h3>
            <p class="text-sm text-gray-600 mb-4">Please provide a reason for your refund request. We'll review it within 2-3 business days.</p>
            <form id="refundRequestForm">
                <div class="mb-4">
                    <label for="refundReason" class="block text-sm font-medium text-gray-700 mb-2">
                        Reason for refund <span class="text-red-500">*</span>
                    </label>
                    <textarea id="refundReason" name="reason" rows="4" required
                              class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-accent focus:border-transparent"
                              placeholder="Please explain why you need a refund..."></textarea>
                </div>
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="closeRefundModal()" 
                            class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400 transition-colors">
                        Cancel
                    </button>
                    <button type="submit" 
                            class="px-4 py-2 bg-orange-600 text-white rounded-md hover:bg-orange-700 transition-colors">
                        Submit Request
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
function cancelOrder() {
    document.getElementById('cancelOrderModal').classList.remove('hidden');
}

function closeCancelModal() {
    document.getElementById('cancelOrderModal').classList.add('hidden');
    document.getElementById('cancelOrderForm').reset();
}

function requestRefund() {
    document.getElementById('refundRequestModal').classList.remove('hidden');
}

function closeRefundModal() {
    document.getElementById('refundRequestModal').classList.add('hidden');
    document.getElementById('refundRequestForm').reset();
}

function contactSupport() {
    // You can implement this to open a support chat or redirect to contact page
    alert('Support feature coming soon! Please email support@sejiskitchen.com for assistance.');
}

// Cancel Order Form
document.getElementById('cancelOrderForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const reason = document.getElementById('cancelReason').value;
    const submitBtn = e.target.querySelector('button[type="submit"]');
    const originalText = submitBtn.textContent;
    
    submitBtn.disabled = true;
    submitBtn.textContent = 'Cancelling...';
    
    try {
        const response = await fetch(`/user/orders/{{ $order->order_no }}/cancel`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ reason })
        });
        
        const data = await response.json();
        
        if (data.success) {
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

// Refund Request Form
document.getElementById('refundRequestForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const reason = document.getElementById('refundReason').value;
    const submitBtn = e.target.querySelector('button[type="submit"]');
    const originalText = submitBtn.textContent;
    
    submitBtn.disabled = true;
    submitBtn.textContent = 'Submitting...';
    
    try {
        const response = await fetch(`/user/orders/{{ $order->order_no }}/refund`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ reason })
        });
        
        const data = await response.json();
        
        if (data.success) {
            alert('Refund request submitted successfully');
            window.location.reload();
        } else {
            alert(data.message || 'Unable to submit refund request');
        }
    } catch (error) {
        alert('An error occurred. Please try again.');
    } finally {
        submitBtn.disabled = false;
        submitBtn.textContent = originalText;
        closeRefundModal();
    }
});

// Close modals when clicking outside
document.getElementById('cancelOrderModal').addEventListener('click', function(e) {
    if (e.target === this) closeCancelModal();
});

document.getElementById('refundRequestModal').addEventListener('click', function(e) {
    if (e.target === this) closeRefundModal();
});
</script>
@endpush
@endsection
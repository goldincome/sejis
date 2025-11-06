@extends('layouts.admin')
@section('header', 'Order Details')

@php
    use App\Enums\ProductTypeEnum; // Import the Enum to use in the view
@endphp

@section('content')
<main class="flex-1 p-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Order Details</h1>
        <a href="{{ route('admin.orders.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded-lg transition duration-300">
            <i class="fas fa-arrow-left mr-2"></i>Back to Orders
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Order Details -->
        <div class="lg:col-span-2 bg-white shadow-md rounded-lg p-6">
            <div class="flex justify-between items-start mb-4 border-b pb-4">
                <div>
                    <h2 class="text-2xl font-semibold text-gray-800">Order #{{ $order->order_no }}</h2>
                    <p class="text-sm text-gray-500">Placed on {{ $order->created_at->format('F j, Y, g:i a') }}</p>
                </div>
                <div>
                     <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                        {{ ucfirst($order->status->value) }}
                    </span>
                </div>
            </div>

            <h3 class="text-lg font-semibold text-gray-700 mb-4">Items Ordered</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left font-medium text-gray-500 uppercase tracking-wider">Product</th>
                            <th class="px-4 py-2 text-left font-medium text-gray-500 uppercase tracking-wider">Price</th>
                            <th class="px-4 py-2 text-left font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                            <th class="px-4 py-2 text-right font-medium text-gray-500 uppercase tracking-wider">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($order->orderDetails as $detail)
                        <tr>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <p class="font-medium text-gray-900">{{ $detail->name }}</p>
                                <p class="text-gray-500 text-xs">{{ $detail->product_type }}</p>

                                {{-- START: ADDED DYNAMIC CONTENT --}}
                                <div class="mt-2 text-xs text-gray-600 space-y-1">
                                    @if($detail->product_type == ProductTypeEnum::ITEM_RENTAL->value)
                                        <!-- EQUIPMENT RENTAL DETAILS -->
                                        <p><strong>Start Date:</strong> {{ \Carbon\Carbon::parse($detail->start_date)->format('M d, Y') }}</p>
                                        <p><strong>End Date:</strong> {{ \Carbon\Carbon::parse($detail->end_date)->format('M d, Y') }}</p>
                                        @php
                                            $startDate = \Carbon\Carbon::parse($detail->start_date);
                                            $endDate = \Carbon\Carbon::parse($detail->end_date);
                                            $durationInDays = $startDate->diffInDays($endDate) + 1;
                                        @endphp
                                        <p><strong>Duration:</strong> {{ $durationInDays }} day(s)</p>
                                    
                                    @elseif($detail->product_type == ProductTypeEnum::KITCHEN_RENTAL->value)
                                        <!-- KITCHEN RENTAL DETAILS -->
                                        <p><strong>Booking Date:</strong> {{ \Carbon\Carbon::parse($detail->booked_date)->format('M d, Y') }}</p>
                                        @if($detail->booked_durations)
                                            @php
                                                // Decode the JSON string
                                                $durationData = json_decode($detail->booked_durations, true);
                                                // Check if 'time_slot' key exists
                                                $timeSlot = $durationData['time_slot'] ?? 'N/A';
                                            @endphp
                                            <p><strong>Time:</strong> {{ $timeSlot }}</p>
                                        @endif
                                    @endif
                                </div>
                                {{-- END: ADDED DYNAMIC CONTENT --}}

                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">{{ $order->currency }} {{ number_format($detail->price, 2) }}</td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                {{ $detail->quantity }} 
                                @if($detail->product_type == ProductTypeEnum::ITEM_RENTAL->value) 
                                    unit(s)
                                @elseif($detail->product_type == ProductTypeEnum::KITCHEN_RENTAL->value)
                                    hour(s)
                                @endif
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-right">{{ $order->currency }} {{ number_format($detail->sub_total, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="flex justify-end mt-6">
                <div class="w-full max-w-sm">
                    <div class="flex justify-between py-2 border-b">
                        <span class="text-gray-600">Subtotal</span>
                        <span class="font-semibold text-gray-900">{{ $order->currency }} {{ number_format($order->sub_total, 2) }}</span>
                    </div>
                    <div class="flex justify-between py-2 border-b">
                        <span class="text-gray-600">Tax</span>
                        <span class="font-semibold text-gray-900">{{ $order->currency }} {{ number_format($order->tax, 2) }}</span>
                    </div>
                    <div class="flex justify-between py-2 text-lg font-bold">
                        <span class="text-gray-900">Total</span>
                        <span class="text-gray-900">{{ $order->currency }} {{ number_format($order->total, 2) }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Customer & Payment Details -->
        <div class="lg:col-span-1 space-y-6">
            <div class="bg-white shadow-md rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-800 border-b pb-3 mb-4">Customer</h3>
                @if($order->user)
                <p class="font-semibold text-gray-900">{{ $order->user->name }}</p>
                <p class="text-sm text-gray-600">{{ $order->user->email }}</p>
                <p class="text-sm text-gray-600">Customer No: {{ $order->user->customer_no }}</p>
                @else
                <p class="text-gray-500">User not found.</p>
                @endif
            </div>
             <div class="bg-white shadow-md rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-800 border-b pb-3 mb-4">Payment Information</h3>
                <p class="text-sm text-gray-600">
                    <span class="font-semibold">Method:</span> {{ $order->payment_method }}
                </p>
                <p class="text-sm text-gray-600">
                    <span class="font-semibold">Transaction ID:</span>
                </p>
                <p class="text-xs text-gray-800 break-words">{{ $order->payment_method_order_id ?? 'N/A' }}</p>
            </div>
        </div>
    </div>
</main>
@endsection
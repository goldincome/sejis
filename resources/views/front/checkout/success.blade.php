@extends('layouts.app') 

@section('title')
    Order Successful   - Sejis Rental
@endsection

@section('description')
    Order Successful - Sejis Rental
@endsection

@section('content')
    <main class="container mx-auto px-6 py-16 md:py-24 text-center">
        <div class="bg-white p-8 md:p-12 rounded-lg shadow-xl max-w-2xl mx-auto border border-green-300">
            <div class="mx-auto flex items-center justify-center h-20 w-20 rounded-full bg-green-100 mb-6">
                <i class="fas fa-check-circle text-5xl text-green-500"></i>
            </div>
            <h1 class="text-3xl md:text-4xl font-bold text-green-600 mb-4">Order Successful!</h1>
            <p class="text-gray-700 text-lg mb-6">
                Thank you for your purchase. Your order is being processed.
            </p>

            <div class="bg-blue-50 p-6 rounded-md text-left my-8 border border-blue-200">
                <h2 class="text-xl font-semibold text-blue-700 mb-4">Order Summary:</h2>
                <div class="space-y-2 text-gray-600 mb-4">
                    <p><strong>Order ID:</strong> #{{ $order->order_no }}</p>
                    <p><strong>Date:</strong> {{ $order->created_at->format('D, M j, Y') }}</p>
                </div>
            
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left text-gray-700 border">
                        <thead class="bg-blue-100 text-blue-800 font-semibold">
                            <tr>
                                <th class="p-3 border">Product</th>
                                <th class="p-3 border">Qty</th>
                                <th class="p-3 border">Price</th>
                                <th class="p-3 border">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white">
                            @foreach ($order->orderDetails as $orderDetail)
                                <tr>
                                    <td class="p-3 border">{{ $orderDetail->name }}</td>
                                    <td class="p-3 border">{{ $orderDetail->quantity }}</td>
                                    <td class="p-3 border">{{ currencyFormatter($orderDetail->price) }}</td>
                                    <td class="p-3 border">{{ currencyFormatter($orderDetail->price * $orderDetail->quantity) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            
                <div class="mt-6 space-y-1 text-gray-700 text-right">
                    <p><strong>Tax:</strong> {{ currencyFormatter($order->tax) }}</p>
                    <p class="text-lg font-semibold text-blue-700"><strong>Total:</strong> {{ currencyFormatter($order->total) }}</p>
                </div>
            
                <p class="text-sm text-gray-500 mt-4">You will receive an email confirmation shortly with the full details of your
                    order.</p>
            </div>


            <div class="mt-10 space-y-4 md:space-y-0 md:flex md:justify-center md:space-x-4">
                <a href="#" class="block w-full md:w-auto bg-orange-500 hover:bg-orange-600 text-white font-semibold py-3 px-6 rounded-lg transition duration-300 shadow-md text-lg">
                    View Full Order Details <i class="fas fa-receipt ml-2"></i>
                </a>
                <a href="{{ url('/') }}" class="block w-full md:w-auto bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-6 rounded-lg transition duration-300 shadow-md text-lg">
                    <i class="fas fa-arrow-left mr-2"></i> Back to Homepage
                </a>
            </div>
            <p class="text-sm text-gray-500 mt-8">
                If you have any questions, please <a href="contact-us.html" class="text-orange-500 hover:underline">contact our support team</a>.
            </p>
        </div>
    </main>
@endsection



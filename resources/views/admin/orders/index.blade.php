@extends('layouts.admin')
@section('header', 'All Orders')
@section('content')
<main class="flex-1 p-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Orders</h1>
    </div>

    <div class="bg-white shadow-md rounded-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full leading-normal">
                <thead class="bg-gray-800 text-white">
                    <tr>
                        <th class="px-5 py-3 border-b-2 border-gray-200 text-left text-xs font-semibold uppercase tracking-wider">Order #</th>
                        <th class="px-5 py-3 border-b-2 border-gray-200 text-left text-xs font-semibold uppercase tracking-wider">Customer</th>
                        <th class="px-5 py-3 border-b-2 border-gray-200 text-left text-xs font-semibold uppercase tracking-wider">Total</th>
                        <th class="px-5 py-3 border-b-2 border-gray-200 text-left text-xs font-semibold uppercase tracking-wider">Payment</th>
                        <th class="px-5 py-3 border-b-2 border-gray-200 text-left text-xs font-semibold uppercase tracking-wider">Status</th>
                        <th class="px-5 py-3 border-b-2 border-gray-200 text-left text-xs font-semibold uppercase tracking-wider">Date</th>
                        <th class="px-5 py-3 border-b-2 border-gray-200 text-left text-xs font-semibold uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($orders as $order)
                    <tr class="hover:bg-gray-100">
                        <td class="px-5 py-4 border-b border-gray-200 bg-white text-sm">
                            <p class="text-gray-900 whitespace-no-wrap font-semibold">{{ $order->order_no }}</p>
                        </td>
                        <td class="px-5 py-4 border-b border-gray-200 bg-white text-sm">
                            <p class="text-gray-900 whitespace-no-wrap">{{ $order->user->name ?? 'N/A' }}</p>
                        </td>
                        <td class="px-5 py-4 border-b border-gray-200 bg-white text-sm">
                            <p class="text-gray-900 whitespace-no-wrap">{{ $order->currency }} {{ number_format($order->total, 2) }}</p>
                        </td>
                        <td class="px-5 py-4 border-b border-gray-200 bg-white text-sm">
                             <p class="text-gray-900 whitespace-no-wrap">{{ $order->payment_method }}</p>
                        </td>
                        <td class="px-5 py-4 border-b border-gray-200 bg-white text-sm">
                            <span class="relative inline-block px-3 py-1 font-semibold text-green-900 leading-tight">
                                <span aria-hidden class="absolute inset-0 bg-green-200 opacity-50 rounded-full"></span>
                                <span class="relative">{{ ucfirst($order->status->value) }}</span>
                            </span>
                        </td>
                        <td class="px-5 py-4 border-b border-gray-200 bg-white text-sm">
                            <p class="text-gray-900 whitespace-no-wrap">{{ $order->created_at->format('M d, Y') }}</p>
                        </td>
                        <td class="px-5 py-4 border-b border-gray-200 bg-white text-sm">
                            <div class="flex items-center space-x-2">
                                <a href="{{ route('admin.orders.show', $order) }}" class="text-blue-500 hover:text-blue-700" title="View">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('admin.orders.edit', $order) }}" class="text-yellow-500 hover:text-yellow-700" title="Edit">
                                    <i class="fas fa-pencil-alt"></i>
                                </a>
                                <form action="{{ route('admin.orders.destroy', $order) }}" method="POST" onsubmit="return confirm('Are you sure? This will also delete order details.');" class="inline-block">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-500 hover:text-red-700" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-10 text-gray-500">
                            No orders found.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-5 py-5 bg-white border-t flex flex-col xs:flex-row items-center xs:justify-between">
            {{ $orders->links() }}
        </div>
    </div>
</main>
@endsection

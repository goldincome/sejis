@extends('layouts.user')
@section('title', 'My Dashboard')
@section('page_title', 'My Dashboard')
@section('page_intro', 'Welcome back! Here\'s your account overview.')

@push('styles')
<style>
.stat-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    transform: translateY(0);
    transition: all 0.3s ease;
}
.stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 20px 40px rgba(0,0,0,0.1);
}
.chart-container {
    height: 300px;
}
</style>
@endpush

@section('content')
<div class="lg:col-span-3">
    <!-- Profile Completion Alert -->
    {{--
    @if(isset($profileCompletion) && $profileCompletion['percentage'] < 100)
    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                </svg>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-yellow-800">Complete Your Profile</h3>
                <p class="text-sm text-yellow-700 mt-1">
                    Your profile is {{ $profileCompletion['percentage'] }}% complete. 
                    <a href="{{ route('user.profile.edit') }}" class="font-medium underline">Complete it now</a> to unlock all features.
                </p>
            </div>
        </div>
    </div>
    @endif
    --}}
    <div class="bg-white p-8 rounded-2xl shadow-2xl space-y-8 section-animate">
        
        <!-- Statistics Overview -->
        <div>
            <h2 class="text-2xl font-bold text-brand-deep-ash mb-6">Account Overview</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Total Orders -->
                <div class="bg-gradient-to-r from-orange-400 to-blue-500 text-white p-6 rounded-lg text-center transform hover:scale-105 transition-transform">
                    <div class="text-3xl font-bold mb-2">{{ $stats['total_orders'] }}</div>
                    <div class="text-sm opacity-90">Total Orders</div>
                    <div class="text-xs opacity-75 mt-1">All time</div>
                </div>

                <!-- Upcoming Rentals -->
                <div class="bg-gradient-to-r from-green-400 to-blue-500 text-white p-6 rounded-lg text-center transform hover:scale-105 transition-transform">
                    <div class="text-3xl font-bold mb-2">{{ $stats['upcoming_rentals'] }}</div>
                    <div class="text-sm opacity-90">Upcoming Rentals</div>
                    <div class="text-xs opacity-75 mt-1">Next 30 days</div>
                </div>

                <!-- Total Spent -->
                <div class="bg-gradient-to-r from-purple-400 to-pink-500 text-white p-6 rounded-lg text-center transform hover:scale-105 transition-transform">
                    <div class="text-3xl font-bold mb-2">£{{ number_format($stats['total_spent'], 2) }}</div>
                    <div class="text-sm opacity-90">Total Spent</div>
                    <div class="text-xs opacity-75 mt-1">All time</div>
                </div>

                <!-- Completion Rate -->
                <div class="bg-gradient-to-r from-orange-400 to-red-500 text-white p-6 rounded-lg text-center transform hover:scale-105 transition-transform">
                    <div class="text-3xl font-bold mb-2">{{ $stats['completion_rate'] }}%</div>
                    <div class="text-sm opacity-90">Success Rate</div>
                    <div class="text-xs opacity-75 mt-1">Order completion</div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <a href="{{ route('user.orders') }}" class="bg-green-50 hover:bg-green-100 p-4 rounded-lg text-center transition-colors">
                <div class="text-green-600 text-2xl mb-2">📋</div>
                <div class="font-medium text-gray-900">My Orders</div>
                <div class="text-sm text-gray-600">View order history</div>
            </a>
            
            <a href="{{ route('user.profile.edit') }}" class="bg-purple-50 hover:bg-purple-100 p-4 rounded-lg text-center transition-colors">
                <div class="text-purple-600 text-2xl mb-2">👤</div>
                <div class="font-medium text-gray-900">My Profile</div>
                <div class="text-sm text-gray-600">Update information</div>
            </a>

            <a href="{{ route('kitchen-rentals.index') }}" class="bg-blue-50 hover:bg-blue-100 p-4 rounded-lg text-center transition-colors">
                <div class="text-blue-600 text-2xl mb-2">🍳</div>
                <div class="font-medium text-gray-900">Book Kitchen</div>
                <div class="text-sm text-gray-600">Find available slots</div>
            </a>

            <a href="{{ route('equipment-rentals.index') }}" class="bg-blue-50 hover:bg-blue-100 p-4 rounded-lg text-center transition-colors">
                <div class="text-blue-600 text-2xl mb-2">🍳</div>
                <div class="font-medium text-gray-900">Book Equipment</div>
                <div class="text-sm text-gray-600">Find available slots</div>
            </a>
        </div>

        <!-- Recent Orders -->
        <div>
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold text-brand-deep-ash">Recent Orders</h2>
                <a href="{{ route('user.orders') }}" class="text-accent hover:underline font-medium">View All Orders</a>
            </div>
            
            @if($recentOrders->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($recentOrders as $order)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">#{{ $order->order_no }}</div>
                                <div class="text-sm text-gray-500">{{ $order->orderDetails->count() }} item(s)</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $order->created_at->format('M d, Y') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                £{{ number_format($order->total, 2) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @php
                                    $statusColors = [
                                        'pending' => 'bg-yellow-100 text-yellow-800',
                                        'paid' => 'bg-blue-100 text-blue-800',
                                        'confirmed' => 'bg-green-100 text-green-800',
                                        'completed' => 'bg-green-100 text-green-800',
                                        'canceled' => 'bg-red-100 text-red-800',
                                        'failed' => 'bg-red-100 text-red-800'
                                    ];
                                    $statusColor = $statusColors[$order->status->value] ?? 'bg-gray-100 text-gray-800';
                                @endphp
                                <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full {{ $statusColor }}">
                                    {{ ucfirst($order->status->value) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <a href="{{ route('user.order.details', $order->order_no) }}" 
                                   class="text-accent hover:underline font-medium">View Details</a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="text-center py-12">
                <div class="text-gray-400 text-6xl mb-4">📋</div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">No orders yet</h3>
                <p class="text-gray-600 mb-6">Start by booking your first kitchen rental</p>
                <a href="{{ route('kitchen-rentals.index') }}" 
                   class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-accent hover:bg-accent-dark transition-colors">
                    Browse Kitchens
                </a>
            </div>
            @endif
        </div>

        <!-- Upcoming Bookings -->
        @if($upcomingOrders->count() > 0)
        <div>
            <h2 class="text-2xl font-bold text-brand-deep-ash mb-6">Upcoming Kitchen Rentals</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($upcomingOrders as $order)
                    @foreach($order->orderDetails as $detail)
                        @if($detail->booked_date && \Carbon\Carbon::parse($detail->booked_date)->isFuture())
                        <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                            <div class="flex items-center justify-between mb-3">
                                <h3 class="font-medium text-gray-900">{{ $detail->name }}</h3>
                                <span class="text-sm text-gray-500">#{{ $order->order_no }}</span>
                            </div>
                            <div class="space-y-2 text-sm text-gray-600">
                                <div class="flex items-center">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                    {{ \Carbon\Carbon::parse($detail->booked_date)->format('M d, Y') }}
                                </div>
                                @if($detail->booked_durations)
                                <div class="flex items-center">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    {{ json_decode($detail->booked_durations, true)['time_slot'] ?? 'Full day' }}
                                </div>
                                @endif
                            </div>
                            <div class="mt-4 pt-3 border-t border-gray-100">
                                <a href="{{ route('user.order.details', $order->order_no) }}" 
                                   class="text-accent hover:underline text-sm font-medium">View Details →</a>
                            </div>
                        </div>
                        @endif
                    @endforeach
                @endforeach
            </div>
        </div>
        @endif

        <!-- Loyalty Program -->
        {{--
        <div class="bg-gradient-to-r from-purple-50 to-pink-50 p-6 rounded-lg border border-purple-100">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900">Loyalty Program</h3>
                <span class="px-3 py-1 bg-purple-100 text-purple-800 text-sm font-medium rounded-full">
                    {{ $loyaltyInfo['tier'] ?? 'Bronze' }} Member
                </span>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-center">
                <div>
                    <div class="text-2xl font-bold text-purple-600">{{ $loyaltyInfo['points'] ?? 0 }}</div>
                    <div class="text-sm text-gray-600">Points Earned</div>
                </div>
                <div>
                    <div class="text-2xl font-bold text-purple-600">{{ count($loyaltyInfo['benefits'] ?? []) }}</div>
                    <div class="text-sm text-gray-600">Active Benefits</div>
                </div>
                <div>
                    <div class="text-2xl font-bold text-purple-600">
                        @if(isset($loyaltyInfo['next_tier_threshold']))
                            £{{ number_format($loyaltyInfo['next_tier_threshold'] - $stats['total_spent'], 2) }}
                        @else
                            Max Tier!
                        @endif
                    </div>
                    <div class="text-sm text-gray-600">
                        @if(isset($loyaltyInfo['next_tier_threshold']))
                            To Next Tier
                        @else
                            Highest Tier
                        @endif
                    </div>
                </div>
            </div>
        </div>
        --}}
    </div>
</div>

@push('scripts')
<script>
// Add any dashboard-specific JavaScript here
document.addEventListener('DOMContentLoaded', function() {
    // Auto-refresh certain elements every 30 seconds
    setInterval(function() {
        // You can add AJAX calls here to refresh stats
    }, 30000);
});
</script>
@endpush
@endsection
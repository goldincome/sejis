@extends('layouts.user')
@section('title', 'My Dashboard')
@section('page_title', 'My Dashboard')
@section('page_intro', 'Welcome, Manage everything here.')
@section('content')
    <div class="lg:col-span-3">
        <div class="bg-white p-8 rounded-2xl shadow-2xl space-y-12 section-animate">
            <!-- Welcome / Quick Stats -->
            <div>
                <h2 class="text-2xl font-bold text-brand-deep-ash mb-6">Overview</h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="bg-brand-light-blue/50 p-6 rounded-lg text-center">
                        <p class="text-4xl font-bold text-brand-deep-ash">2</p>
                        <p class="text-sm font-medium text-gray-600">Upcoming Bookings</p>
                    </div>
                    <div class="bg-brand-light-blue/50 p-6 rounded-lg text-center">
                        <p class="text-4xl font-bold text-brand-deep-ash">15</p>
                        <p class="text-sm font-medium text-gray-600">Completed Orders</p>
                    </div>
                    <div class="bg-brand-light-blue/50 p-6 rounded-lg text-center">
                        <p class="text-4xl font-bold text-brand-deep-ash">$1,850</p>
                        <p class="text-sm font-medium text-gray-600">Total Spent</p>
                    </div>
                </div>
            </div>

            <!-- Upcoming Bookings Section -->
            <div>
                <h2 class="text-2xl font-bold text-brand-deep-ash mb-6">Upcoming Bookings</h2>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Item</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">The ProChef
                                    Station</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">June 28, 2025</td>
                                <td class="px-6 py-4 whitespace-nowrap"><span
                                        class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Confirmed</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm"><a href="user-panel-order-details.html"
                                        class="text-accent hover:underline">View Details</a></td>
                            </tr>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Professional
                                    Cookware Set</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">July 05, 2025</td>
                                <td class="px-6 py-4 whitespace-nowrap"><span
                                        class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Confirmed</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm"><a href="user-panel-order-details.html"
                                        class="text-accent hover:underline">View Details</a></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>

@endsection

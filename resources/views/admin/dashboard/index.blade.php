 @extends('layouts.admin')

@section('content')
    <!-- Page Content -->
    <main class="flex-1 p-4 sm:p-8 bg-gray-100">
        <!-- Quick Stats -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="bg-white p-6 rounded-lg shadow-md flex items-center justify-between">
                <div>
                    <p class="text-4xl font-bold text-accent">12</p>
                    <p class="text-sm text-gray-500">Pending Orders</p>
                </div>
                <i class="fas fa-hourglass-half text-4xl text-brand-light-blue"></i>
            </div>
            <div class="bg-white p-6 rounded-lg shadow-md flex items-center justify-between">
                <div>
                    <p class="text-4xl font-bold text-green-500">85</p>
                    <p class="text-sm text-gray-500">Completed Orders</p>
                </div>
                <i class="fas fa-check-circle text-4xl text-brand-light-blue"></i>
            </div>
            <div class="bg-white p-6 rounded-lg shadow-md flex items-center justify-between">
                <div>
                    <p class="text-4xl font-bold text-brand-deep-ash">$5,430</p>
                    <p class="text-sm text-gray-500">Revenue (Month)</p>
                </div>
                <i class="fas fa-dollar-sign text-4xl text-brand-light-blue"></i>
            </div>
            <div class="bg-white p-6 rounded-lg shadow-md flex items-center justify-between">
                <div>
                    <p class="text-4xl font-bold text-blue-500">32</p>
                    <p class="text-sm text-gray-500">New Users</p>
                </div>
                <i class="fas fa-user-plus text-4xl text-brand-light-blue"></i>
            </div>
        </div>

        <!-- Recent Orders Table -->
        <div class="bg-white p-4 sm:p-8 rounded-lg shadow-md">
            <h2 class="text-xl font-bold text-brand-deep-ash mb-6">Recent Pending Orders</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order
                                ID</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Customer</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">PK-12355ABC</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">Jane Smith</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">June 18, 2025</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-bold">$165.00</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm"><a href="admin-approve-order.html"
                                    class="text-accent hover:underline">Approve / View</a></td>
                        </tr>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">PK-12354XYZ</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">Mike Johnson</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">June 18, 2025</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-bold">$30.00</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm"><a href="admin-approve-order.html"
                                    class="text-accent hover:underline">Approve / View</a></td>
                        </tr>
                        <!-- Add more rows as needed -->
                    </tbody>
                </table>
            </div>
        </div>
    </main>
@endsection

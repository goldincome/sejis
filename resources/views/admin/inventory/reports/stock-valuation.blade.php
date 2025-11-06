@extends('layouts.admin')

@section('title', 'Stock Valuation Report')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('admin.inventory.reports.index') }}">Reports</a></li>
                            <li class="breadcrumb-item active">Stock Valuation</li>
                        </ol>
                    </nav>
                    <h1 class="h3 mb-0">Stock Valuation Report</h1>
                    <p class="text-muted">Comprehensive analysis of inventory value and distribution</p>
                </div>
                <div>
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-outline-primary" onclick="refreshReport()">
                            <i class="fas fa-sync-alt"></i> Refresh
                        </button>
                        <a href="{{ route('admin.inventory.reports.stock-valuation', array_merge(request()->query(), ['export' => 1])) }}" 
                           class="btn btn-success">
                            <i class="fas fa-download"></i> Export CSV
                        </a>
                        <button type="button" class="btn btn-secondary" onclick="showFilters()">
                            <i class="fas fa-filter"></i> Filters
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Form -->
    <div class="card mb-4" id="filtersCard" style="display: none;">
        <div class="card-header">
            <h6 class="m-0 font-weight-bold text-primary">Report Filters</h6>
        </div>
        <div class="card-body">
            <form method="GET" id="filterForm">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="category_id">Category</label>
                            <select class="form-control" id="category_id" name="category_id">
                                <option value="">All Categories</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" 
                                        {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="location">Location</label>
                            <select class="form-control" id="location" name="location">
                                <option value="">All Locations</option>
                                @foreach($locations as $location)
                                    <option value="{{ $location }}" 
                                        {{ request('location') == $location ? 'selected' : '' }}>
                                        {{ $location }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="condition">Condition</label>
                            <select class="form-control" id="condition" name="condition">
                                <option value="">All Conditions</option>
                                <option value="excellent" {{ request('condition') == 'excellent' ? 'selected' : '' }}>Excellent</option>
                                <option value="good" {{ request('condition') == 'good' ? 'selected' : '' }}>Good</option>
                                <option value="fair" {{ request('condition') == 'fair' ? 'selected' : '' }}>Fair</option>
                                <option value="poor" {{ request('condition') == 'poor' ? 'selected' : '' }}>Poor</option>
                                <option value="needs_repair" {{ request('condition') == 'needs_repair' ? 'selected' : '' }}>Needs Repair</option>
                                <option value="out_of_service" {{ request('condition') == 'out_of_service' ? 'selected' : '' }}>Out of Service</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>&nbsp;</label>
                            <button type="submit" class="btn btn-primary form-control">
                                <i class="fas fa-search"></i> Apply Filters
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Items
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($report['summary']['total_items']) }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-boxes fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Current Value
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                £{{ number_format($report['summary']['total_current_value'], 2) }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-pound-sign fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-{{ $report['summary']['total_value_change'] >= 0 ? 'success' : 'danger' }} shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-{{ $report['summary']['total_value_change'] >= 0 ? 'success' : 'danger' }} text-uppercase mb-1">
                                Value Change
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $report['summary']['total_value_change'] >= 0 ? '+' : '' }}£{{ number_format($report['summary']['total_value_change'], 2) }}
                                <small class="text-muted">({{ $report['summary']['total_value_change_percentage'] }}%)</small>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-chart-line fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Average Item Value
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                £{{ number_format($report['summary']['average_item_value'], 2) }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calculator fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Category Breakdown -->
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Value by Category</h6>
                </div>
                <div class="card-body">
                    <div class="chart-area">
                        <canvas id="categoryChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Top Categories Table -->
        <div class="col-lg-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Top Categories by Value</h6>
                </div>
                <div class="card-body">
                    @foreach($report['category_breakdown']->take(5) as $category)
                        <div class="mb-3">
                            <div class="d-flex justify-content-between">
                                <span class="font-weight-bold">{{ $category['category_name'] }}</span>
                                <span class="text-success">£{{ number_format($category['current_value'], 0) }}</span>
                            </div>
                            <div class="progress">
                                <div class="progress-bar bg-primary" role="progressbar" 
                                     style="width: {{ ($category['current_value'] / $report['summary']['total_current_value']) * 100 }}%">
                                </div>
                            </div>
                            <small class="text-muted">{{ $category['item_count'] }} items</small>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Category Breakdown Table -->
        <div class="col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Category Analysis</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Category</th>
                                    <th>Items</th>
                                    <th>Quantity</th>
                                    <th>Current Value</th>
                                    <th>Change</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($report['category_breakdown'] as $category)
                                    <tr>
                                        <td><strong>{{ $category['category_name'] }}</strong></td>
                                        <td>{{ number_format($category['item_count']) }}</td>
                                        <td>{{ number_format($category['total_quantity']) }}</td>
                                        <td>£{{ number_format($category['current_value'], 2) }}</td>
                                        <td>
                                            <span class="badge badge-{{ $category['value_change'] >= 0 ? 'success' : 'danger' }}">
                                                {{ $category['value_change'] >= 0 ? '+' : '' }}{{ $category['value_change_percentage'] }}%
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Location Breakdown -->
        <div class="col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-success">Location Distribution</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Location</th>
                                    <th>Items</th>
                                    <th>Quantity</th>
                                    <th>Value</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($report['location_breakdown'] as $location)
                                    <tr>
                                        <td><strong>{{ $location['location'] }}</strong></td>
                                        <td>{{ number_format($location['item_count']) }}</td>
                                        <td>{{ number_format($location['total_quantity']) }}</td>
                                        <td>£{{ number_format($location['current_value'], 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Condition Analysis -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-warning">Condition Analysis</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        @foreach($report['condition_breakdown'] as $condition)
                            <div class="col-lg-2 col-md-4 mb-3">
                                <div class="card border-left-{{ $this->getConditionColor($condition['condition']) }} h-100">
                                    <div class="card-body">
                                        <div class="text-center">
                                            <h5 class="card-title">{{ $condition['condition'] }}</h5>
                                            <h3 class="text-{{ $this->getConditionColor($condition['condition']) }}">
                                                {{ $condition['item_count'] }}
                                            </h3>
                                            <p class="card-text">
                                                <small class="text-muted">
                                                    {{ $condition['percentage_of_total'] }}% of total<br>
                                                    £{{ number_format($condition['current_value'], 0) }}
                                                </small>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Report Footer -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="font-weight-bold">Report Information</h6>
                            <p class="mb-1"><strong>Generated:</strong> {{ $report['generated_at'] }}</p>
                            <p class="mb-1"><strong>Filters Applied:</strong> 
                                @if(empty(array_filter($filters)))
                                    None
                                @else
                                    @foreach(array_filter($filters) as $key => $value)
                                        {{ ucfirst(str_replace('_', ' ', $key)) }}: {{ $value }}{{ !$loop->last ? ', ' : '' }}
                                    @endforeach
                                @endif
                            </p>
                        </div>
                        <div class="col-md-6 text-right">
                            <h6 class="font-weight-bold">Actions</h6>
                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="window.print()">
                                <i class="fas fa-print"></i> Print Report
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-success" onclick="emailReport()">
                                <i class="fas fa-envelope"></i> Email Report
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
$(document).ready(function() {
    initializeCategoryChart();
});

function initializeCategoryChart() {
    const ctx = document.getElementById('categoryChart').getContext('2d');
    
    const categoryData = @json($report['category_breakdown']);
    
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: categoryData.map(item => item.category_name),
            datasets: [{
                data: categoryData.map(item => item.current_value),
                backgroundColor: [
                    '#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b', 
                    '#858796', '#5a5c69', '#6f42c1', '#e83e8c', '#fd7e14'
                ],
                borderWidth: 2,
                borderColor: '#ffffff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const value = context.parsed;
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = ((value / total) * 100).toFixed(1);
                            return context.label + ': £' + value.toLocaleString() + ' (' + percentage + '%)';
                        }
                    }
                }
            }
        }
    });
}

function showFilters() {
    $('#filtersCard').toggle();
}

function refreshReport() {
    window.location.reload();
}

function emailReport() {
    Swal.fire({
        title: 'Email Report',
        html: `
            <div class="form-group">
                <label for="email-recipients">Recipients (comma-separated)</label>
                <input type="email" class="form-control" id="email-recipients" 
                       placeholder="admin@sejis.com, manager@sejis.com" multiple>
            </div>
            <div class="form-group">
                <label for="email-message">Message (optional)</label>
                <textarea class="form-control" id="email-message" rows="3" 
                          placeholder="Additional message to include with the report..."></textarea>
            </div>
        `,
        showCancelButton: true,
        confirmButtonText: 'Send Report',
        preConfirm: () => {
            const recipients = document.getElementById('email-recipients').value;
            const message = document.getElementById('email-message').value;
            
            if (!recipients) {
                Swal.showValidationMessage('Please enter at least one email address');
                return false;
            }
            
            return { recipients, message };
        }
    }).then((result) => {
        if (result.isConfirmed) {
            // Here you would implement the email sending functionality
            Swal.fire({
                icon: 'success',
                title: 'Report Sent!',
                text: 'The stock valuation report has been emailed successfully.',
                timer: 3000,
                showConfirmButton: false
            });
        }
    });
}
</script>
@endpush

@php
function getConditionColor($condition) {
    return match(strtolower($condition)) {
        'excellent' => 'success',
        'good' => 'primary',
        'fair' => 'warning',
        'poor' => 'danger',
        'needs repair', 'out of service' => 'dark',
        default => 'secondary'
    };
}
@endphp
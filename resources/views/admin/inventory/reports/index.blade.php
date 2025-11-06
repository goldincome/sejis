@extends('layouts.admin')

@section('title', 'Inventory Reports & Analytics')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0">Inventory Reports & Analytics</h1>
                    <p class="text-muted">Comprehensive inventory analysis and reporting dashboard</p>
                </div>
                <div>
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-primary" onclick="generateComprehensiveReport()">
                            <i class="fas fa-chart-line"></i> Comprehensive Report
                        </button>
                        <button type="button" class="btn btn-outline-secondary" onclick="scheduleReports()">
                            <i class="fas fa-clock"></i> Schedule Reports
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Stats Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Inventory Value
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="total-inventory-value">
                                <i class="fas fa-spinner fa-spin"></i> Loading...
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
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Monthly Movements
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="monthly-movements">
                                <i class="fas fa-spinner fa-spin"></i> Loading...
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-exchange-alt fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Low Stock Items
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="low-stock-count">
                                <i class="fas fa-spinner fa-spin"></i> Loading...
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                Potential Revenue Loss
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="revenue-loss">
                                <i class="fas fa-spinner fa-spin"></i> Loading...
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-chart-line-down fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Report Navigation Cards -->
    <div class="row">
        <!-- Stock Valuation Report -->
        <div class="col-lg-4 col-md-6 mb-4">
            <div class="card shadow h-100">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-pound-sign"></i> Stock Valuation
                    </h6>
                    <div class="dropdown no-arrow">
                        <a class="dropdown-toggle" href="#" role="button" data-toggle="dropdown">
                            <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in">
                            <a class="dropdown-item" href="{{ route('admin.inventory.reports.stock-valuation') }}">
                                <i class="fas fa-eye fa-sm fa-fw mr-2 text-gray-400"></i> View Report
                            </a>
                            <a class="dropdown-item" href="{{ route('admin.inventory.reports.stock-valuation', ['export' => 1]) }}">
                                <i class="fas fa-download fa-sm fa-fw mr-2 text-gray-400"></i> Export CSV
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <p class="card-text">Analyze inventory value by category, location, and condition. Track value changes and depreciation over time.</p>
                    <ul class="list-unstyled mb-3">
                        <li><i class="fas fa-check text-success"></i> Total inventory valuation</li>
                        <li><i class="fas fa-check text-success"></i> Category-wise breakdown</li>
                        <li><i class="fas fa-check text-success"></i> Value change analysis</li>
                        <li><i class="fas fa-check text-success"></i> Location distribution</li>
                    </ul>
                    <a href="{{ route('admin.inventory.reports.stock-valuation') }}" class="btn btn-primary btn-block">
                        <i class="fas fa-chart-bar"></i> Generate Report
                    </a>
                </div>
            </div>
        </div>

        <!-- Movement Analytics -->
        <div class="col-lg-4 col-md-6 mb-4">
            <div class="card shadow h-100">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-success">
                        <i class="fas fa-exchange-alt"></i> Movement Analytics
                    </h6>
                    <div class="dropdown no-arrow">
                        <a class="dropdown-toggle" href="#" role="button" data-toggle="dropdown">
                            <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in">
                            <a class="dropdown-item" href="{{ route('admin.inventory.reports.movement-analytics') }}">
                                <i class="fas fa-eye fa-sm fa-fw mr-2 text-gray-400"></i> View Report
                            </a>
                            <a class="dropdown-item" href="javascript:void(0)" onclick="generateMovementReport()">
                                <i class="fas fa-download fa-sm fa-fw mr-2 text-gray-400"></i> Export CSV
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <p class="card-text">Track inventory movements, trends, and activity patterns. Analyze stock flow and usage patterns.</p>
                    <ul class="list-unstyled mb-3">
                        <li><i class="fas fa-check text-success"></i> Daily movement trends</li>
                        <li><i class="fas fa-check text-success"></i> Movement type analysis</li>
                        <li><i class="fas fa-check text-success"></i> Product activity ranking</li>
                        <li><i class="fas fa-check text-success"></i> User activity tracking</li>
                    </ul>
                    <a href="{{ route('admin.inventory.reports.movement-analytics') }}" class="btn btn-success btn-block">
                        <i class="fas fa-chart-line"></i> Generate Report
                    </a>
                </div>
            </div>
        </div>

        <!-- Stock Turnover Analysis -->
        <div class="col-lg-4 col-md-6 mb-4">
            <div class="card shadow h-100">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-info">
                        <i class="fas fa-sync-alt"></i> Stock Turnover
                    </h6>
                    <div class="dropdown no-arrow">
                        <a class="dropdown-toggle" href="#" role="button" data-toggle="dropdown">
                            <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in">
                            <a class="dropdown-item" href="{{ route('admin.inventory.reports.stock-turnover') }}">
                                <i class="fas fa-eye fa-sm fa-fw mr-2 text-gray-400"></i> View Report
                            </a>
                            <a class="dropdown-item" href="javascript:void(0)" onclick="generateTurnoverReport()">
                                <i class="fas fa-download fa-sm fa-fw mr-2 text-gray-400"></i> Export CSV
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <p class="card-text">Measure inventory efficiency and identify fast/slow-moving products. Optimize stock levels based on turnover rates.</p>
                    <ul class="list-unstyled mb-3">
                        <li><i class="fas fa-check text-success"></i> Turnover ratio calculation</li>
                        <li><i class="fas fa-check text-success"></i> Performance ranking</li>
                        <li><i class="fas fa-check text-success"></i> Days sales inventory</li>
                        <li><i class="fas fa-check text-success"></i> Optimization recommendations</li>
                    </ul>
                    <a href="{{ route('admin.inventory.reports.stock-turnover') }}" class="btn btn-info btn-block">
                        <i class="fas fa-tachometer-alt"></i> Generate Report
                    </a>
                </div>
            </div>
        </div>

        <!-- ABC Analysis -->
        <div class="col-lg-4 col-md-6 mb-4">
            <div class="card shadow h-100">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-warning">
                        <i class="fas fa-layer-group"></i> ABC Analysis
                    </h6>
                    <div class="dropdown no-arrow">
                        <a class="dropdown-toggle" href="#" role="button" data-toggle="dropdown">
                            <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in">
                            <a class="dropdown-item" href="{{ route('admin.inventory.reports.abc-analysis') }}">
                                <i class="fas fa-eye fa-sm fa-fw mr-2 text-gray-400"></i> View Report
                            </a>
                            <a class="dropdown-item" href="{{ route('admin.inventory.reports.abc-analysis', ['export' => 1]) }}">
                                <i class="fas fa-download fa-sm fa-fw mr-2 text-gray-400"></i> Export CSV
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <p class="card-text">Classify inventory using the 80/20 rule. Prioritize management efforts based on item value and importance.</p>
                    <ul class="list-unstyled mb-3">
                        <li><i class="fas fa-check text-success"></i> A/B/C categorization</li>
                        <li><i class="fas fa-check text-success"></i> Value-based priority</li>
                        <li><i class="fas fa-check text-success"></i> Management recommendations</li>
                        <li><i class="fas fa-check text-success"></i> Control strategy guidance</li>
                    </ul>
                    <a href="{{ route('admin.inventory.reports.abc-analysis') }}" class="btn btn-warning btn-block">
                        <i class="fas fa-sort-amount-down"></i> Generate Report
                    </a>
                </div>
            </div>
        </div>

        <!-- Low Stock Impact -->
        <div class="col-lg-4 col-md-6 mb-4">
            <div class="card shadow h-100">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-danger">
                        <i class="fas fa-exclamation-triangle"></i> Low Stock Impact
                    </h6>
                    <div class="dropdown no-arrow">
                        <a class="dropdown-toggle" href="#" role="button" data-toggle="dropdown">
                            <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in">
                            <a class="dropdown-item" href="{{ route('admin.inventory.reports.low-stock-impact') }}">
                                <i class="fas fa-eye fa-sm fa-fw mr-2 text-gray-400"></i> View Report
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <p class="card-text">Assess financial impact of low stock situations. Calculate potential revenue loss and prioritize restocking.</p>
                    <ul class="list-unstyled mb-3">
                        <li><i class="fas fa-check text-success"></i> Revenue impact analysis</li>
                        <li><i class="fas fa-check text-success"></i> Urgency scoring</li>
                        <li><i class="fas fa-check text-success"></i> Demand pattern analysis</li>
                        <li><i class="fas fa-check text-success"></i> Restocking priorities</li>
                    </ul>
                    <a href="{{ route('admin.inventory.reports.low-stock-impact') }}" class="btn btn-danger btn-block">
                        <i class="fas fa-chart-line-down"></i> Generate Report
                    </a>
                </div>
            </div>
        </div>

        <!-- Comprehensive Report -->
        <div class="col-lg-4 col-md-6 mb-4">
            <div class="card shadow h-100 border-left-primary">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-file-alt"></i> Comprehensive Report
                    </h6>
                </div>
                <div class="card-body">
                    <p class="card-text">Generate a complete inventory analysis combining all report types into a single comprehensive document.</p>
                    <ul class="list-unstyled mb-3">
                        <li><i class="fas fa-check text-success"></i> All-in-one analysis</li>
                        <li><i class="fas fa-check text-success"></i> Executive summary</li>
                        <li><i class="fas fa-check text-success"></i> Actionable insights</li>
                        <li><i class="fas fa-check text-success"></i> Customizable sections</li>
                    </ul>
                    <button type="button" class="btn btn-primary btn-block" onclick="generateComprehensiveReport()">
                        <i class="fas fa-file-pdf"></i> Generate Report
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Report History -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Recent Report Activity</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" id="recentReportsTable">
                            <thead>
                                <tr>
                                    <th>Report Type</th>
                                    <th>Generated By</th>
                                    <th>Date & Time</th>
                                    <th>Parameters</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td colspan="5" class="text-center">
                                        <em>Report history will be displayed here</em>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Comprehensive Report Modal -->
<div class="modal fade" id="comprehensiveReportModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Generate Comprehensive Report</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="comprehensiveReportForm">
                <div class="modal-body">
                    @csrf
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="report_start_date">Start Date</label>
                                <input type="date" class="form-control" id="report_start_date" name="start_date" 
                                       value="{{ now()->subDays(30)->format('Y-m-d') }}" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="report_end_date">End Date</label>
                                <input type="date" class="form-control" id="report_end_date" name="end_date" 
                                       value="{{ now()->format('Y-m-d') }}" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Include Sections</label>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="include_valuation" id="include_valuation" checked>
                                    <label class="form-check-label" for="include_valuation">
                                        Stock Valuation
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="include_movements" id="include_movements" checked>
                                    <label class="form-check-label" for="include_movements">
                                        Movement Analytics
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="include_turnover" id="include_turnover" checked>
                                    <label class="form-check-label" for="include_turnover">
                                        Stock Turnover
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="include_abc" id="include_abc" checked>
                                    <label class="form-check-label" for="include_abc">
                                        ABC Analysis
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="include_low_stock" id="include_low_stock" checked>
                                    <label class="form-check-label" for="include_low_stock">
                                        Low Stock Impact
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-file-alt"></i> Generate Report
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    loadDashboardWidgets();
    
    // Comprehensive report form submission
    $('#comprehensiveReportForm').on('submit', function(e) {
        e.preventDefault();
        submitComprehensiveReport();
    });
});

function loadDashboardWidgets() {
    fetch('{{ route("admin.inventory.reports.dashboard-widgets") }}', {
        method: 'GET',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const widgets = data.widgets;
            
            // Update stock valuation
            document.getElementById('total-inventory-value').innerHTML = 
                '£' + new Intl.NumberFormat().format(widgets.stock_valuation.total_value);
            
            // Update monthly movements
            document.getElementById('monthly-movements').innerHTML = 
                new Intl.NumberFormat().format(widgets.recent_movements.total_movements);
            
            // Update low stock count
            document.getElementById('low-stock-count').innerHTML = 
                new Intl.NumberFormat().format(widgets.low_stock_impact.items_count);
            
            // Update revenue loss
            document.getElementById('revenue-loss').innerHTML = 
                '£' + new Intl.NumberFormat().format(widgets.low_stock_impact.potential_revenue_loss);
        }
    })
    .catch(error => {
        console.error('Failed to load dashboard widgets:', error);
        // Set error state for widgets
        document.getElementById('total-inventory-value').innerHTML = 'Error';
        document.getElementById('monthly-movements').innerHTML = 'Error';
        document.getElementById('low-stock-count').innerHTML = 'Error';
        document.getElementById('revenue-loss').innerHTML = 'Error';
    });
}

function generateComprehensiveReport() {
    $('#comprehensiveReportModal').modal('show');
}

function submitComprehensiveReport() {
    const formData = new FormData(document.getElementById('comprehensiveReportForm'));
    const submitBtn = document.querySelector('#comprehensiveReportForm button[type="submit"]');
    
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Generating...';
    submitBtn.disabled = true;
    
    fetch('{{ route("admin.inventory.reports.comprehensive") }}', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => {
        if (response.ok) {
            return response.blob();
        }
        throw new Error('Report generation failed');
    })
    .then(blob => {
        // Create download link
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.style.display = 'none';
        a.href = url;
        a.download = 'comprehensive_inventory_report_' + new Date().toISOString().split('T')[0] + '.pdf';
        document.body.appendChild(a);
        a.click();
        window.URL.revokeObjectURL(url);
        
        $('#comprehensiveReportModal').modal('hide');
        
        Swal.fire({
            icon: 'success',
            title: 'Report Generated!',
            text: 'Your comprehensive report has been downloaded.',
            timer: 3000,
            showConfirmButton: false
        });
    })
    .catch(error => {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Failed to generate comprehensive report'
        });
    })
    .finally(() => {
        submitBtn.innerHTML = '<i class="fas fa-file-alt"></i> Generate Report';
        submitBtn.disabled = false;
    });
}

function generateMovementReport() {
    const startDate = prompt('Enter start date (YYYY-MM-DD):', '{{ now()->subDays(30)->format("Y-m-d") }}');
    const endDate = prompt('Enter end date (YYYY-MM-DD):', '{{ now()->format("Y-m-d") }}');
    
    if (startDate && endDate) {
        window.open(`{{ route('admin.inventory.reports.movement-analytics') }}?start_date=${startDate}&end_date=${endDate}&export=1`, '_blank');
    }
}

function generateTurnoverReport() {
    const startDate = prompt('Enter start date (YYYY-MM-DD):', '{{ now()->subDays(90)->format("Y-m-d") }}');
    const endDate = prompt('Enter end date (YYYY-MM-DD):', '{{ now()->format("Y-m-d") }}');
    
    if (startDate && endDate) {
        window.open(`{{ route('admin.inventory.reports.stock-turnover') }}?start_date=${startDate}&end_date=${endDate}&export=1`, '_blank');
    }
}

function scheduleReports() {
    Swal.fire({
        title: 'Schedule Automated Reports',
        text: 'This feature will be available in the next update.',
        icon: 'info',
        confirmButtonText: 'Got it!'
    });
}
</script>
@endpush
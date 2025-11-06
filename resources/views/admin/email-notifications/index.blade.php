@extends('layouts.admin')

@section('title', 'Email Notifications Management')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0">Email Notifications Management</h1>
                    <p class="text-muted">Preview, test, and configure email notifications</p>
                </div>
                <div>
                    <a href="{{ route('admin.email-notifications.configuration') }}" class="btn btn-outline-primary">
                        <i class="fas fa-cog"></i> Configuration
                    </a>
                    <a href="{{ route('admin.email-notifications.settings') }}" class="btn btn-primary">
                        <i class="fas fa-envelope-open-text"></i> Email Settings
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-left-primary">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Order Confirmations (This Month)
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $stats['order_confirmations'] ?? 0 }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-left-success">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Status Updates Sent
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $stats['status_updates'] ?? 0 }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-sync-alt fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-left-info">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Welcome Emails
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $stats['welcome_emails'] ?? 0 }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-user-plus fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-left-warning">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Failed Deliveries
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $stats['failed_emails'] ?? 0 }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Email Templates Management -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Email Templates</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- Order Confirmation Email -->
                        <div class="col-md-6 col-lg-4 mb-4">
                            <div class="card border-left-primary h-100">
                                <div class="card-body">
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="icon-circle bg-primary text-white mr-3">
                                            <i class="fas fa-receipt"></i>
                                        </div>
                                        <div>
                                            <h5 class="card-title mb-0">Order Confirmation</h5>
                                            <small class="text-muted">Sent after order placement</small>
                                        </div>
                                    </div>
                                    <p class="card-text text-sm">Confirms order details, payment information, and provides order tracking.</p>
                                    <div class="btn-group w-100" role="group">
                                        <a href="{{ route('admin.email-notifications.preview', 'order_confirmation') }}" 
                                           class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-eye"></i> Preview
                                        </a>
                                        <button type="button" class="btn btn-sm btn-primary" 
                                                onclick="sendTestEmail('order_confirmation')">
                                            <i class="fas fa-paper-plane"></i> Test
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Order Status Update Email -->
                        <div class="col-md-6 col-lg-4 mb-4">
                            <div class="card border-left-success h-100">
                                <div class="card-body">
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="icon-circle bg-success text-white mr-3">
                                            <i class="fas fa-sync-alt"></i>
                                        </div>
                                        <div>
                                            <h5 class="card-title mb-0">Status Update</h5>
                                            <small class="text-muted">Sent on order status changes</small>
                                        </div>
                                    </div>
                                    <p class="card-text text-sm">Notifies customers about order progression and delivery updates.</p>
                                    <div class="btn-group w-100" role="group">
                                        <a href="{{ route('admin.email-notifications.preview', 'status_update') }}" 
                                           class="btn btn-sm btn-outline-success">
                                            <i class="fas fa-eye"></i> Preview
                                        </a>
                                        <button type="button" class="btn btn-sm btn-success" 
                                                onclick="sendTestEmail('status_update')">
                                            <i class="fas fa-paper-plane"></i> Test
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Admin Notification Email -->
                        <div class="col-md-6 col-lg-4 mb-4">
                            <div class="card border-left-warning h-100">
                                <div class="card-body">
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="icon-circle bg-warning text-white mr-3">
                                            <i class="fas fa-bell"></i>
                                        </div>
                                        <div>
                                            <h5 class="card-title mb-0">Admin Alerts</h5>
                                            <small class="text-muted">Internal notifications</small>
                                        </div>
                                    </div>
                                    <p class="card-text text-sm">Critical alerts for new orders, payment issues, and system events.</p>
                                    <div class="btn-group w-100" role="group">
                                        <a href="{{ route('admin.email-notifications.preview', 'admin_notification') }}" 
                                           class="btn btn-sm btn-outline-warning">
                                            <i class="fas fa-eye"></i> Preview
                                        </a>
                                        <button type="button" class="btn btn-sm btn-warning" 
                                                onclick="sendTestEmail('admin_notification')">
                                            <i class="fas fa-paper-plane"></i> Test
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Welcome Email -->
                        <div class="col-md-6 col-lg-4 mb-4">
                            <div class="card border-left-info h-100">
                                <div class="card-body">
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="icon-circle bg-info text-white mr-3">
                                            <i class="fas fa-user-plus"></i>
                                        </div>
                                        <div>
                                            <h5 class="card-title mb-0">Welcome Email</h5>
                                            <small class="text-muted">Sent to new customers</small>
                                        </div>
                                    </div>
                                    <p class="card-text text-sm">Welcomes new users with account setup and getting started guide.</p>
                                    <div class="btn-group w-100" role="group">
                                        <a href="{{ route('admin.email-notifications.preview', 'welcome_email') }}" 
                                           class="btn btn-sm btn-outline-info">
                                            <i class="fas fa-eye"></i> Preview
                                        </a>
                                        <button type="button" class="btn btn-sm btn-info" 
                                                onclick="sendTestEmail('welcome_email')">
                                            <i class="fas fa-paper-plane"></i> Test
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Test Email Form -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Send Test Email</h6>
                </div>
                <div class="card-body">
                    <form id="testEmailForm">
                        @csrf
                        <div class="form-row">
                            <div class="form-group col-md-4">
                                <label for="email_type">Email Type</label>
                                <select class="form-control" id="email_type" name="email_type" required>
                                    <option value="">Select email type...</option>
                                    <option value="order_confirmation">Order Confirmation</option>
                                    <option value="status_update">Status Update</option>
                                    <option value="admin_notification">Admin Notification</option>
                                    <option value="welcome_email">Welcome Email</option>
                                </select>
                            </div>
                            <div class="form-group col-md-4">
                                <label for="test_email">Test Email Address</label>
                                <input type="email" class="form-control" id="test_email" name="test_email" 
                                       placeholder="test@example.com" required>
                            </div>
                            <div class="form-group col-md-4">
                                <label>&nbsp;</label>
                                <button type="submit" class="btn btn-primary form-control">
                                    <i class="fas fa-paper-plane"></i> Send Test Email
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Test Email Modal -->
<div class="modal fade" id="testEmailModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Send Test Email</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="quickTestForm">
                    @csrf
                    <input type="hidden" id="modal_email_type" name="email_type">
                    <div class="form-group">
                        <label for="modal_test_email">Email Address</label>
                        <input type="email" class="form-control" id="modal_test_email" name="test_email" 
                               placeholder="test@example.com" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="submitQuickTest()">
                    <i class="fas fa-paper-plane"></i> Send Test
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.border-left-primary { border-left: 0.25rem solid #4e73df !important; }
.border-left-success { border-left: 0.25rem solid #1cc88a !important; }
.border-left-info { border-left: 0.25rem solid #36b9cc !important; }
.border-left-warning { border-left: 0.25rem solid #f6c23e !important; }

.icon-circle {
    height: 2.5rem;
    width: 2.5rem;
    border-radius: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.text-sm { font-size: 0.875rem; }
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    // Test email form submission
    $('#testEmailForm').on('submit', function(e) {
        e.preventDefault();
        sendTestEmailForm(this);
    });
});

function sendTestEmail(emailType) {
    $('#modal_email_type').val(emailType);
    $('#modal_test_email').val('');
    $('#testEmailModal').modal('show');
}

function submitQuickTest() {
    const form = document.getElementById('quickTestForm');
    sendTestEmailForm(form, true);
}

function sendTestEmailForm(form, isModal = false) {
    const formData = new FormData(form);
    const submitBtn = isModal ? 
        document.querySelector('#testEmailModal .btn-primary') : 
        form.querySelector('button[type="submit"]');
    
    // Show loading state
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending...';
    submitBtn.disabled = true;
    
    fetch('{{ route("admin.email-notifications.send-test") }}', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: 'Test Email Sent!',
                text: data.message,
                timer: 3000,
                showConfirmButton: false
            });
            
            if (isModal) {
                $('#testEmailModal').modal('hide');
            } else {
                form.reset();
            }
        } else {
            throw new Error(data.message || 'Failed to send test email');
        }
    })
    .catch(error => {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: error.message || 'Failed to send test email'
        });
    })
    .finally(() => {
        // Restore button state
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    });
}
</script>
@endpush
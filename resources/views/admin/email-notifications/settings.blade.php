@extends('layouts.admin')

@section('title', 'Email Notification Settings')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0">Email Notification Settings</h1>
                    <p class="text-muted">Configure email notification preferences and recipients</p>
                </div>
                <div>
                    <a href="{{ route('admin.email-notifications.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Emails
                    </a>
                    <button type="button" class="btn btn-primary" onclick="saveAllSettings()">
                        <i class="fas fa-save"></i> Save All Settings
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Notification Toggle Settings -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Email Notification Controls</h6>
                </div>
                <div class="card-body">
                    <form id="notificationToggleForm">
                        @csrf
                        <div class="row">
                            <div class="col-md-6">
                                <!-- Customer Notifications -->
                                <h6 class="font-weight-bold text-gray-800 mb-3">Customer Notifications</h6>
                                <div class="form-group">
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" class="custom-control-input" 
                                               id="order_confirmation_enabled" name="order_confirmation_enabled" 
                                               {{ setting('order_confirmation_enabled', true) ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="order_confirmation_enabled">
                                            Order Confirmation Emails
                                        </label>
                                    </div>
                                    <small class="form-text text-muted">Send confirmation emails when orders are placed</small>
                                </div>

                                <div class="form-group">
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" class="custom-control-input" 
                                               id="order_status_updates_enabled" name="order_status_updates_enabled" 
                                               {{ setting('order_status_updates_enabled', true) ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="order_status_updates_enabled">
                                            Order Status Updates
                                        </label>
                                    </div>
                                    <small class="form-text text-muted">Send emails when order status changes</small>
                                </div>

                                <div class="form-group">
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" class="custom-control-input" 
                                               id="welcome_emails_enabled" name="welcome_emails_enabled" 
                                               {{ setting('welcome_emails_enabled', true) ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="welcome_emails_enabled">
                                            Welcome Emails
                                        </label>
                                    </div>
                                    <small class="form-text text-muted">Send welcome emails to new customers</small>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <!-- Admin Notifications -->
                                <h6 class="font-weight-bold text-gray-800 mb-3">Admin Notifications</h6>
                                <div class="form-group">
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" class="custom-control-input" 
                                               id="admin_new_order_alerts" name="admin_new_order_alerts" 
                                               {{ setting('admin_new_order_alerts', true) ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="admin_new_order_alerts">
                                            New Order Alerts
                                        </label>
                                    </div>
                                    <small class="form-text text-muted">Alert admins when new orders are placed</small>
                                </div>

                                <div class="form-group">
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" class="custom-control-input" 
                                               id="admin_payment_alerts" name="admin_payment_alerts" 
                                               {{ setting('admin_payment_alerts', true) ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="admin_payment_alerts">
                                            Payment Issue Alerts
                                        </label>
                                    </div>
                                    <small class="form-text text-muted">Alert admins about payment failures</small>
                                </div>

                                <div class="form-group">
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" class="custom-control-input" 
                                               id="admin_high_value_alerts" name="admin_high_value_alerts" 
                                               {{ setting('admin_high_value_alerts', true) ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="admin_high_value_alerts">
                                            High Value Order Alerts
                                        </label>
                                    </div>
                                    <small class="form-text text-muted">Alert for orders above threshold amount</small>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Email Recipients Settings -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Admin Email Recipients</h6>
                </div>
                <div class="card-body">
                    <form id="emailRecipientsForm">
                        @csrf
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="admin_email">General Admin Email</label>
                                    <input type="email" class="form-control" id="admin_email" name="admin_email" 
                                           value="{{ setting('admin_email', 'admin@sejiskitchenrental.com') }}" required>
                                    <small class="form-text text-muted">Primary admin email for general notifications</small>
                                </div>

                                <div class="form-group">
                                    <label for="operations_admin_email">Operations Admin Email</label>
                                    <input type="email" class="form-control" id="operations_admin_email" name="operations_admin_email" 
                                           value="{{ setting('operations_admin_email', 'operations@sejiskitchenrental.com') }}">
                                    <small class="form-text text-muted">Email for order and kitchen rental operations</small>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="finance_admin_email">Finance Admin Email</label>
                                    <input type="email" class="form-control" id="finance_admin_email" name="finance_admin_email" 
                                           value="{{ setting('finance_admin_email', 'finance@sejiskitchenrental.com') }}">
                                    <small class="form-text text-muted">Email for payment and financial notifications</small>
                                </div>

                                <div class="form-group">
                                    <label for="support_admin_email">Support Admin Email</label>
                                    <input type="email" class="form-control" id="support_admin_email" name="support_admin_email" 
                                           value="{{ setting('support_admin_email', 'support@sejiskitchenrental.com') }}">
                                    <small class="form-text text-muted">Email for customer support notifications</small>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Email Configuration Settings -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Email Behavior Settings</h6>
                </div>
                <div class="card-body">
                    <form id="emailBehaviorForm">
                        @csrf
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="high_value_threshold">High Value Order Threshold (£)</label>
                                    <input type="number" class="form-control" id="high_value_threshold" name="high_value_threshold" 
                                           value="{{ setting('high_value_threshold', 1000) }}" min="0" step="0.01">
                                    <small class="form-text text-muted">Orders above this amount trigger high value alerts</small>
                                </div>

                                <div class="form-group">
                                    <label for="email_retry_attempts">Email Retry Attempts</label>
                                    <input type="number" class="form-control" id="email_retry_attempts" name="email_retry_attempts" 
                                           value="{{ setting('email_retry_attempts', 3) }}" min="1" max="10">
                                    <small class="form-text text-muted">Number of times to retry failed email deliveries</small>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="email_retry_delay">Retry Delay (minutes)</label>
                                    <input type="number" class="form-control" id="email_retry_delay" name="email_retry_delay" 
                                           value="{{ setting('email_retry_delay', 5) }}" min="1" max="60">
                                    <small class="form-text text-muted">Minutes to wait between retry attempts</small>
                                </div>

                                <div class="form-group">
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" class="custom-control-input" 
                                               id="email_queue_enabled" name="email_queue_enabled" 
                                               {{ setting('email_queue_enabled', true) ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="email_queue_enabled">
                                            Use Email Queue
                                        </label>
                                    </div>
                                    <small class="form-text text-muted">Queue emails for background processing (recommended)</small>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Welcome Email Settings -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Welcome Email Settings</h6>
                </div>
                <div class="card-body">
                    <form id="welcomeEmailForm">
                        @csrf
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" class="custom-control-input" 
                                               id="welcome_discount_enabled" name="welcome_discount_enabled" 
                                               {{ setting('welcome_discount_enabled', true) ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="welcome_discount_enabled">
                                            Include Welcome Discount
                                        </label>
                                    </div>
                                    <small class="form-text text-muted">Offer discount to new customers</small>
                                </div>

                                <div class="form-group">
                                    <label for="welcome_discount_amount">Discount Amount (%)</label>
                                    <input type="number" class="form-control" id="welcome_discount_amount" name="welcome_discount_amount" 
                                           value="{{ setting('welcome_discount_amount', 10) }}" min="0" max="100">
                                    <small class="form-text text-muted">Percentage discount for new customers</small>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="welcome_discount_code">Discount Code</label>
                                    <input type="text" class="form-control" id="welcome_discount_code" name="welcome_discount_code" 
                                           value="{{ setting('welcome_discount_code', 'WELCOME10') }}">
                                    <small class="form-text text-muted">Discount code to include in welcome emails</small>
                                </div>

                                <div class="form-group">
                                    <label for="welcome_discount_expiry">Discount Expiry (days)</label>
                                    <input type="number" class="form-control" id="welcome_discount_expiry" name="welcome_discount_expiry" 
                                           value="{{ setting('welcome_discount_expiry', 30) }}" min="1" max="365">
                                    <small class="form-text text-muted">Number of days the welcome discount is valid</small>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Save Actions -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="font-weight-bold text-gray-800 mb-1">Save Settings</h6>
                            <small class="text-muted">Changes will be applied immediately after saving</small>
                        </div>
                        <div>
                            <button type="button" class="btn btn-secondary" onclick="resetAllSettings()">
                                <i class="fas fa-undo"></i> Reset to Defaults
                            </button>
                            <button type="button" class="btn btn-primary" onclick="saveAllSettings()">
                                <i class="fas fa-save"></i> Save All Settings
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
<script>
function saveAllSettings() {
    const forms = [
        'notificationToggleForm',
        'emailRecipientsForm', 
        'emailBehaviorForm',
        'welcomeEmailForm'
    ];
    
    const allFormData = new FormData();
    
    // Collect data from all forms
    forms.forEach(formId => {
        const form = document.getElementById(formId);
        const formData = new FormData(form);
        for (let [key, value] of formData.entries()) {
            allFormData.append(key, value);
        }
    });
    
    // Add CSRF token
    allFormData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
    
    // Show loading state
    const saveBtn = document.querySelector('button[onclick="saveAllSettings()"]');
    const originalText = saveBtn.innerHTML;
    saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
    saveBtn.disabled = true;
    
    fetch('{{ route("admin.email-notifications.settings.update") }}', {
        method: 'POST',
        body: allFormData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: 'Settings Saved!',
                text: data.message,
                timer: 3000,
                showConfirmButton: false
            });
        } else {
            throw new Error(data.message || 'Failed to save settings');
        }
    })
    .catch(error => {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: error.message || 'Failed to save settings'
        });
    })
    .finally(() => {
        // Restore button state
        saveBtn.innerHTML = originalText;
        saveBtn.disabled = false;
    });
}

function resetAllSettings() {
    Swal.fire({
        title: 'Reset Settings?',
        text: 'This will reset all email notification settings to their default values.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, reset them!'
    }).then((result) => {
        if (result.isConfirmed) {
            // Reset form values to defaults
            // This is a simplified version - you might want to reload the page
            // or fetch default values from the server
            location.reload();
        }
    });
}

// Auto-save on toggle changes
$(document).ready(function() {
    $('.custom-control-input').on('change', function() {
        // Optional: Auto-save individual settings
        // You can implement this if you want immediate saving
    });
});
</script>
@endpush
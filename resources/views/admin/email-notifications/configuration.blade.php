@extends('layouts.admin')

@section('title', 'Email Configuration')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0">Email Configuration</h1>
                    <p class="text-muted">Test and verify email server settings</p>
                </div>
                <div>
                    <a href="{{ route('admin.email-notifications.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Emails
                    </a>
                    <button type="button" class="btn btn-primary" onclick="testEmailConfiguration()">
                        <i class="fas fa-paper-plane"></i> Test Configuration
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Configuration Status -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Current Email Configuration</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <th width="30%">Mail Driver:</th>
                                    <td>
                                        <span class="badge badge-{{ $config['driver'] === 'smtp' ? 'success' : 'warning' }}">
                                            {{ strtoupper($config['driver']) }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Mail Host:</th>
                                    <td>{{ $config['host'] ?? 'Not set' }}</td>
                                </tr>
                                <tr>
                                    <th>Mail Port:</th>
                                    <td>{{ $config['port'] ?? 'Not set' }}</td>
                                </tr>
                                <tr>
                                    <th>Encryption:</th>
                                    <td>
                                        <span class="badge badge-{{ $config['encryption'] ? 'success' : 'warning' }}">
                                            {{ $config['encryption'] ?: 'None' }}
                                        </span>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <th width="30%">From Address:</th>
                                    <td>{{ $config['from']['address'] ?? 'Not set' }}</td>
                                </tr>
                                <tr>
                                    <th>From Name:</th>
                                    <td>{{ $config['from']['name'] ?? 'Not set' }}</td>
                                </tr>
                                <tr>
                                    <th>Username:</th>
                                    <td>{{ $config['username'] ? str_repeat('*', strlen($config['username'])) : 'Not set' }}</td>
                                </tr>
                                <tr>
                                    <th>Password:</th>
                                    <td>{{ $config['password'] ? str_repeat('*', 8) : 'Not set' }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Test Results -->
    <div class="row mb-4" id="testResults" style="display: none;">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Configuration Test Results</h6>
                </div>
                <div class="card-body" id="testResultsContent">
                    <!-- Test results will be populated here -->
                </div>
            </div>
        </div>
    </div>

    <!-- Configuration Test Form -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Test Email Configuration</h6>
                </div>
                <div class="card-body">
                    <form id="configTestForm">
                        @csrf
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="test_email">Test Email Address</label>
                                <input type="email" class="form-control" id="test_email" name="test_email" 
                                       placeholder="admin@example.com" required>
                                <small class="form-text text-muted">
                                    A test email will be sent to this address to verify configuration.
                                </small>
                            </div>
                            <div class="form-group col-md-6">
                                <label for="test_type">Test Type</label>
                                <select class="form-control" id="test_type" name="test_type">
                                    <option value="connection">Connection Test Only</option>
                                    <option value="send_email">Send Test Email</option>
                                    <option value="full_test">Full Configuration Test</option>
                                </select>
                                <small class="form-text text-muted">
                                    Choose the type of test to perform.
                                </small>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-play"></i> Run Test
                            </button>
                            <button type="button" class="btn btn-secondary" onclick="clearTestResults()">
                                <i class="fas fa-eraser"></i> Clear Results
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Troubleshooting Guide -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Troubleshooting Guide</h6>
                </div>
                <div class="card-body">
                    <div class="accordion" id="troubleshootingAccordion">
                        <!-- Connection Issues -->
                        <div class="card">
                            <div class="card-header" id="connectionIssues">
                                <h5 class="mb-0">
                                    <button class="btn btn-link" type="button" data-toggle="collapse" 
                                            data-target="#collapseConnection">
                                        <i class="fas fa-wifi"></i> Connection Issues
                                    </button>
                                </h5>
                            </div>
                            <div id="collapseConnection" class="collapse" data-parent="#troubleshootingAccordion">
                                <div class="card-body">
                                    <ul>
                                        <li>Verify your SMTP host and port settings</li>
                                        <li>Check if your server allows outbound connections on the SMTP port</li>
                                        <li>Ensure firewall settings allow SMTP connections</li>
                                        <li>Try different encryption methods (TLS/SSL)</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <!-- Authentication Issues -->
                        <div class="card">
                            <div class="card-header" id="authIssues">
                                <h5 class="mb-0">
                                    <button class="btn btn-link collapsed" type="button" data-toggle="collapse" 
                                            data-target="#collapseAuth">
                                        <i class="fas fa-key"></i> Authentication Issues
                                    </button>
                                </h5>
                            </div>
                            <div id="collapseAuth" class="collapse" data-parent="#troubleshootingAccordion">
                                <div class="card-body">
                                    <ul>
                                        <li>Double-check your email credentials</li>
                                        <li>Enable "Less secure app access" for Gmail accounts</li>
                                        <li>Use app-specific passwords for Gmail with 2FA</li>
                                        <li>Verify your email provider's SMTP settings</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <!-- Delivery Issues -->
                        <div class="card">
                            <div class="card-header" id="deliveryIssues">
                                <h5 class="mb-0">
                                    <button class="btn btn-link collapsed" type="button" data-toggle="collapse" 
                                            data-target="#collapseDelivery">
                                        <i class="fas fa-envelope"></i> Email Delivery Issues
                                    </button>
                                </h5>
                            </div>
                            <div id="collapseDelivery" class="collapse" data-parent="#troubleshootingAccordion">
                                <div class="card-body">
                                    <ul>
                                        <li>Check spam/junk folders for test emails</li>
                                        <li>Verify sender domain reputation</li>
                                        <li>Configure SPF, DKIM, and DMARC records</li>
                                        <li>Monitor email logs for delivery errors</li>
                                    </ul>
                                </div>
                            </div>
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
$(document).ready(function() {
    $('#configTestForm').on('submit', function(e) {
        e.preventDefault();
        testEmailConfiguration();
    });
});

function testEmailConfiguration() {
    const form = document.getElementById('configTestForm');
    const formData = new FormData(form);
    const submitBtn = form.querySelector('button[type="submit"]');
    
    // Show loading state
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Testing...';
    submitBtn.disabled = true;
    
    // Show test results section
    document.getElementById('testResults').style.display = 'block';
    document.getElementById('testResultsContent').innerHTML = `
        <div class="text-center">
            <i class="fas fa-spinner fa-spin fa-2x text-primary mb-3"></i>
            <p>Testing email configuration...</p>
        </div>
    `;
    
    fetch('{{ route("admin.email-notifications.test-configuration") }}', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        displayTestResults(data);
        
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: 'Configuration Test Completed',
                text: 'Check the results below for details.',
                timer: 3000,
                showConfirmButton: false
            });
        }
    })
    .catch(error => {
        displayTestResults({
            success: false,
            message: error.message || 'Failed to test configuration',
            results: {}
        });
        
        Swal.fire({
            icon: 'error',
            title: 'Test Failed',
            text: error.message || 'Failed to test email configuration'
        });
    })
    .finally(() => {
        // Restore button state
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    });
}

function displayTestResults(data) {
    const resultContent = document.getElementById('testResultsContent');
    
    let html = `
        <div class="alert alert-${data.success ? 'success' : 'danger'}" role="alert">
            <i class="fas fa-${data.success ? 'check-circle' : 'exclamation-circle'}"></i>
            <strong>${data.success ? 'Success' : 'Failed'}:</strong> ${data.message}
        </div>
    `;
    
    if (data.results) {
        html += '<div class="row">';
        
        // Connection test
        if (data.results.connection !== undefined) {
            html += `
                <div class="col-md-4">
                    <div class="card border-${data.results.connection ? 'success' : 'danger'}">
                        <div class="card-body text-center">
                            <i class="fas fa-${data.results.connection ? 'check-circle text-success' : 'times-circle text-danger'} fa-2x mb-2"></i>
                            <h6>Connection Test</h6>
                            <p class="small">${data.results.connection ? 'Connected successfully' : 'Connection failed'}</p>
                        </div>
                    </div>
                </div>
            `;
        }
        
        // Authentication test
        if (data.results.authentication !== undefined) {
            html += `
                <div class="col-md-4">
                    <div class="card border-${data.results.authentication ? 'success' : 'danger'}">
                        <div class="card-body text-center">
                            <i class="fas fa-${data.results.authentication ? 'check-circle text-success' : 'times-circle text-danger'} fa-2x mb-2"></i>
                            <h6>Authentication</h6>
                            <p class="small">${data.results.authentication ? 'Authentication successful' : 'Authentication failed'}</p>
                        </div>
                    </div>
                </div>
            `;
        }
        
        // Email delivery test
        if (data.results.email_sent !== undefined) {
            html += `
                <div class="col-md-4">
                    <div class="card border-${data.results.email_sent ? 'success' : 'danger'}">
                        <div class="card-body text-center">
                            <i class="fas fa-${data.results.email_sent ? 'check-circle text-success' : 'times-circle text-danger'} fa-2x mb-2"></i>
                            <h6>Email Delivery</h6>
                            <p class="small">${data.results.email_sent ? 'Test email sent successfully' : 'Failed to send test email'}</p>
                        </div>
                    </div>
                </div>
            `;
        }
        
        html += '</div>';
    }
    
    // Add error details if available
    if (data.error_details) {
        html += `
            <div class="mt-3">
                <h6>Error Details:</h6>
                <div class="alert alert-warning">
                    <pre class="mb-0">${data.error_details}</pre>
                </div>
            </div>
        `;
    }
    
    resultContent.innerHTML = html;
}

function clearTestResults() {
    document.getElementById('testResults').style.display = 'none';
    document.getElementById('testResultsContent').innerHTML = '';
}
</script>
@endpush
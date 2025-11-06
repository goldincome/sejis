@extends('emails.layouts.base')

@section('content')
<div class="greeting">
    Admin Alert 🚨
</div>

<div class="content-section">
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px;">
        <h1>{{ $notificationTitle }}</h1>
        <span class="badge badge-{{ $urgencyColor }}">{{ $urgencyLevel }} PRIORITY</span>
    </div>
    
    <div class="alert alert-{{ $urgencyLevel === 'HIGH' ? 'error' : ($urgencyLevel === 'MEDIUM' ? 'warning' : 'success') }}">
        <strong>{{ $notificationMessage }}</strong>
    </div>
</div>

<!-- Order Details -->
<div class="content-section">
    <h3>📋 Order Information</h3>
    <div class="card">
        <table style="width: 100%; margin: 0;">
            <tr>
                <td style="padding: 8px 0; font-weight: 600; width: 40%;">Order Reference:</td>
                <td style="padding: 8px 0;">{{ $order->order_reference }}</td>
            </tr>
            <tr>
                <td style="padding: 8px 0; font-weight: 600;">Order Date:</td>
                <td style="padding: 8px 0;">{{ $orderDate }}</td>
            </tr>
            <tr>
                <td style="padding: 8px 0; font-weight: 600;">Total Amount:</td>
                <td style="padding: 8px 0; font-size: 18px; font-weight: 600; color: var(--brand-deep-ash);">£{{ number_format($orderTotal, 2) }}</td>
            </tr>
            <tr>
                <td style="padding: 8px 0; font-weight: 600;">Payment Method:</td>
                <td style="padding: 8px 0;">{{ $paymentMethod }}</td>
            </tr>
            <tr>
                <td style="padding: 8px 0; font-weight: 600;">Payment Status:</td>
                <td style="padding: 8px 0;">
                    @php
                        $paymentStatusColor = match($order->payment_status) {
                            'completed', 'paid' => 'success',
                            'pending' => 'warning',
                            'failed' => 'error',
                            default => 'secondary'
                        };
                    @endphp
                    <span class="badge badge-{{ $paymentStatusColor }}">{{ $paymentStatus }}</span>
                </td>
            </tr>
            <tr>
                <td style="padding: 8px 0; font-weight: 600;">Order Status:</td>
                <td style="padding: 8px 0;">
                    @php
                        $orderStatusColor = match($order->status) {
                            'confirmed' => 'success',
                            'pending' => 'warning',
                            'cancelled' => 'error',
                            default => 'info'
                        };
                    @endphp
                    <span class="badge badge-{{ $orderStatusColor }}">{{ $orderStatus }}</span>
                </td>
            </tr>
            @if($deliveryDate)
            <tr>
                <td style="padding: 8px 0; font-weight: 600;">Delivery Date:</td>
                <td style="padding: 8px 0;">{{ $deliveryDate }}</td>
            </tr>
            @endif
            @if($returnDate)
            <tr>
                <td style="padding: 8px 0; font-weight: 600;">Return Date:</td>
                <td style="padding: 8px 0;">{{ $returnDate }}</td>
            </tr>
            @endif
        </table>
    </div>
</div>

<!-- Customer Information -->
<div class="content-section">
    <h3>👤 Customer Information</h3>
    <div class="card">
        <table style="width: 100%; margin: 0;">
            <tr>
                <td style="padding: 8px 0; font-weight: 600; width: 30%;">Name:</td>
                <td style="padding: 8px 0;">{{ $customerName }}</td>
            </tr>
            <tr>
                <td style="padding: 8px 0; font-weight: 600;">Email:</td>
                <td style="padding: 8px 0;"><a href="mailto:{{ $customerEmail }}">{{ $customerEmail }}</a></td>
            </tr>
            <tr>
                <td style="padding: 8px 0; font-weight: 600;">Phone:</td>
                <td style="padding: 8px 0;">
                    @if($customerPhone !== 'Not provided')
                        <a href="tel:{{ $customerPhone }}">{{ $customerPhone }}</a>
                    @else
                        {{ $customerPhone }}
                    @endif
                </td>
            </tr>
            @if($deliveryAddress && $deliveryAddress !== 'Not specified')
            <tr>
                <td style="padding: 8px 0; font-weight: 600;">Delivery Address:</td>
                <td style="padding: 8px 0;">{{ $deliveryAddress }}</td>
            </tr>
            @endif
            @if($specialInstructions && $specialInstructions !== 'None')
            <tr>
                <td style="padding: 8px 0; font-weight: 600;">Special Instructions:</td>
                <td style="padding: 8px 0;">{{ $specialInstructions }}</td>
            </tr>
            @endif
        </table>
    </div>
</div>

<!-- Order Items -->
<div class="content-section">
    <h3>📦 Order Items</h3>
    <table class="table">
        <thead>
            <tr>
                <th>Product</th>
                <th style="text-align: center;">Qty</th>
                <th style="text-align: right;">Price</th>
                <th style="text-align: right;">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($orderDetails as $detail)
            <tr>
                <td>
                    <strong>{{ $detail->product->name }}</strong>
                    @if($detail->product->intro)
                        <br><small class="text-muted">{{ $detail->product->intro }}</small>
                    @endif
                </td>
                <td style="text-align: center;">{{ $detail->quantity }}</td>
                <td style="text-align: right;">£{{ number_format($detail->price, 2) }}</td>
                <td style="text-align: right;">£{{ number_format($detail->total_price, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr style="border-top: 2px solid var(--brand-deep-ash);">
                <td colspan="3" style="font-weight: 600; padding-top: 15px;">Total:</td>
                <td style="text-align: right; font-weight: 600; font-size: 18px; padding-top: 15px;">£{{ number_format($orderTotal, 2) }}</td>
            </tr>
        </tfoot>
    </table>
</div>

<!-- Additional Information -->
@if($additionalInfo)
<div class="content-section">
    <h3>ℹ️ Additional Information</h3>
    <div class="alert alert-warning">
        {{ $additionalInfo }}
    </div>
</div>
@endif

<!-- Action Required -->
<div class="content-section">
    <h3>⚠️ Action Required</h3>
    <div class="alert alert-error">
        <strong>{{ $actionRequired }}</strong>
    </div>
</div>

<!-- System Statistics -->
<div class="content-section">
    <h3>📊 Today's System Overview</h3>
    <div class="card">
        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px;">
            <div style="text-align: center; padding: 15px; background-color: var(--brand-light-blue); border-radius: 8px;">
                <div style="font-size: 24px; font-weight: 600; color: var(--brand-deep-ash);">{{ $systemStats['total_orders_today'] }}</div>
                <div style="font-size: 14px; color: var(--text-muted);">Orders Today</div>
            </div>
            <div style="text-align: center; padding: 15px; background-color: #FFF5F5; border-radius: 8px;">
                <div style="font-size: 24px; font-weight: 600; color: var(--error-color);">{{ $systemStats['pending_orders'] }}</div>
                <div style="font-size: 14px; color: var(--text-muted);">Pending Orders</div>
            </div>
            <div style="text-align: center; padding: 15px; background-color: #F0FFF4; border-radius: 8px;">
                <div style="font-size: 24px; font-weight: 600; color: var(--success-color);">£{{ number_format($systemStats['total_revenue_today'], 0) }}</div>
                <div style="font-size: 14px; color: var(--text-muted);">Revenue Today</div>
            </div>
            <div style="text-align: center; padding: 15px; background-color: #FFFAF0; border-radius: 8px;">
                <div style="font-size: 24px; font-weight: 600; color: var(--warning-color);">{{ $systemStats['high_value_orders_count'] }}</div>
                <div style="font-size: 14px; color: var(--text-muted);">High Value Orders</div>
            </div>
        </div>
    </div>
</div>

<!-- Notification Type Specific Content -->
@switch($notificationType)
    @case('new_order')
        <div class="content-section">
            <h3>🎯 Quick Actions for New Orders</h3>
            <ul style="line-height: 1.8;">
                <li>✅ Verify customer information and payment details</li>
                <li>📦 Check inventory availability for ordered items</li>
                <li>📅 Confirm delivery date and logistics</li>
                <li>📞 Contact customer if any clarifications needed</li>
                <li>✅ Update order status to 'confirmed' once processed</li>
            </ul>
        </div>
        @break
    
    @case('payment_failed')
        <div class="content-section">
            <h3>💳 Payment Failure Actions</h3>
            <div class="alert alert-error">
                <strong>Immediate Actions Required:</strong><br>
                • Contact customer within 2 hours<br>
                • Verify payment method and details<br>
                • Offer alternative payment options<br>
                • Set order hold status until payment resolved<br>
                • Document the issue in customer notes
            </div>
        </div>
        @break
    
    @case('high_value_order')
        <div class="content-section">
            <h3>💎 High Value Order Protocol</h3>
            <div class="alert alert-warning">
                <strong>Special Handling Required:</strong><br>
                • Verify customer identity and business credentials<br>
                • Contact customer to confirm order details<br>
                • Check credit limit and payment history<br>
                • Consider requiring deposit or advance payment<br>
                • Assign senior staff member for handling
            </div>
        </div>
        @break
    
    @case('cancellation_request')
        <div class="content-section">
            <h3>❌ Cancellation Processing</h3>
            <div class="alert alert-warning">
                <strong>Cancellation Checklist:</strong><br>
                • Review cancellation policy and timing<br>
                • Calculate any cancellation fees<br>
                • Process refund according to policy<br>
                • Update inventory availability<br>
                • Send cancellation confirmation to customer<br>
                • Document reason for cancellation
            </div>
        </div>
        @break
@endswitch

<!-- Quick Action Buttons -->
<div class="content-section text-center">
    <h3>🚀 Quick Actions</h3>
    <div style="margin: 20px 0;">
        <a href="{{ $orderManageUrl }}" class="btn btn-primary" style="margin: 5px;">
            📋 View Order
        </a>
        
        <a href="{{ $orderEditUrl }}" class="btn btn-secondary" style="margin: 5px;">
            ✏️ Edit Order
        </a>
        
        <a href="{{ $customerManageUrl }}" class="btn btn-secondary" style="margin: 5px;">
            👤 View Customer
        </a>
        
        <a href="{{ $adminDashboardUrl }}" class="btn btn-secondary" style="margin: 5px;">
            🏠 Admin Dashboard
        </a>
    </div>
</div>

<!-- Priority Actions -->
@if($urgencyLevel === 'HIGH')
<div class="content-section">
    <div class="alert alert-error text-center">
        <h3 style="margin: 0; color: var(--error-color);">🚨 HIGH PRIORITY - IMMEDIATE ACTION REQUIRED 🚨</h3>
        <p style="margin: 10px 0 0 0; font-weight: 600;">This notification requires immediate attention. Please address within 1 hour.</p>
    </div>
</div>
@endif

<!-- Contact Information -->
<div class="content-section">
    <div class="card text-center">
        <h4>📱 Emergency Contact</h4>
        <p>For urgent issues outside business hours:</p>
        <p><strong>On-Call Manager:</strong> <a href="tel:+447911123456">+44 7911 123456</a></p>
    </div>
</div>
@endsection
@extends('emails.layouts.base')

@section('content')
<div class="greeting">
    Hello {{ $customerName }}! 👋
</div>

<div class="content-section">
    <h1>📢 Order Status Update</h1>
    <p>We have an update regarding your order <strong>{{ $order->order_reference }}</strong>.</p>
</div>

<!-- Status Update Card -->
<div class="highlight-box text-center">
    <h3 style="margin-top: 0;">🔄 Status Changed</h3>
    <div style="margin: 20px 0;">
        <span class="badge badge-secondary">{{ $oldStatus }}</span>
        <span style="margin: 0 15px; font-size: 18px;">→</span>
        <span class="badge badge-{{ $statusBadgeColor }}">{{ $newStatus }}</span>
    </div>
    
    @if($statusIcon)
    <div style="font-size: 48px; margin: 20px 0;">
        @switch($statusIcon)
            @case('clock')
                ⏰
                @break
            @case('check-circle')
                ✅
                @break
            @case('cog')
                ⚙️
                @break
            @case('box')
                📦
                @break
            @case('truck')
                🚚
                @break
            @case('home')
                🏠
                @break
            @case('check-double')
                ✅✅
                @break
            @case('times-circle')
                ❌
                @break
            @case('undo')
                ↩️
                @break
            @default
                ℹ️
        @endswitch
    </div>
    @endif
    
    <h2 style="color: var(--brand-deep-ash); margin: 15px 0;">{{ $newStatus }}</h2>
    
    @if($statusMessage)
    <p style="font-size: 16px; margin: 15px 0;">{{ $statusMessage }}</p>
    @endif
</div>

<!-- Order Summary -->
<div class="content-section">
    <h3>📋 Order Summary</h3>
    <div class="card">
        <table style="width: 100%; margin: 0;">
            <tr>
                <td style="padding: 8px 0; font-weight: 600;">Order Reference:</td>
                <td style="padding: 8px 0; text-align: right;">{{ $order->order_reference }}</td>
            </tr>
            <tr>
                <td style="padding: 8px 0; font-weight: 600;">Order Date:</td>
                <td style="padding: 8px 0; text-align: right;">{{ $orderDate }}</td>
            </tr>
            <tr>
                <td style="padding: 8px 0; font-weight: 600;">Total Amount:</td>
                <td style="padding: 8px 0; text-align: right; font-weight: 600;">£{{ number_format($orderTotal, 2) }}</td>
            </tr>
            @if($deliveryDate)
            <tr>
                <td style="padding: 8px 0; font-weight: 600;">Delivery Date:</td>
                <td style="padding: 8px 0; text-align: right;">{{ $deliveryDate }}</td>
            </tr>
            @endif
            @if($returnDate)
            <tr>
                <td style="padding: 8px 0; font-weight: 600;">Return Date:</td>
                <td style="padding: 8px 0; text-align: right;">{{ $returnDate }}</td>
            </tr>
            @endif
            <tr>
                <td style="padding: 8px 0; font-weight: 600;">Payment Status:</td>
                <td style="padding: 8px 0; text-align: right;">
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
        </table>
    </div>
</div>

<!-- Update Note -->
@if($updateNote)
<div class="content-section">
    <h3>📝 Additional Information</h3>
    <div class="alert alert-success">
        {{ $updateNote }}
    </div>
</div>
@endif

<!-- Next Steps -->
@if($showNextSteps)
<div class="content-section">
    <h3>🚀 Next Steps</h3>
    <div class="alert alert-success">
        <strong>What to expect next:</strong><br>
        {{ $showNextSteps }}
    </div>
</div>
@endif

<!-- Status-specific content -->
@if($order->status === 'confirmed')
<div class="content-section">
    <h3>🎉 Your Order is Confirmed!</h3>
    <p>Great news! Your order has been confirmed and we're now preparing your rental items. Here's what you can expect:</p>
    
    <ul style="line-height: 1.8; color: var(--text-secondary);">
        <li>✅ Your rental items are reserved and being prepared</li>
        <li>📞 We'll contact you to confirm delivery details</li>
        <li>📦 Delivery will be scheduled as per your requirements</li>
        <li>🛠️ Our team will assist with setup if needed</li>
    </ul>
</div>
@endif

@if($order->status === 'in_transit')
<div class="content-section">
    <h3>🚚 Your Order is On The Way!</h3>
    <p>Your rental items are currently being delivered to your location. Please ensure:</p>
    
    <ul style="line-height: 1.8; color: var(--text-secondary);">
        <li>✅ Someone is available at the delivery address</li>
        <li>📍 Clear access to the setup location</li>
        <li>📞 Your phone is available for delivery updates</li>
        <li>🆔 ID ready for verification if required</li>
    </ul>
    
    @if($trackingAvailable)
    <div class="alert alert-success">
        <strong>Track your delivery:</strong><br>
        Contact us at {{ $contactPhone }} for real-time delivery updates.
    </div>
    @endif
</div>
@endif

@if($order->status === 'delivered')
<div class="content-section">
    <h3>🏠 Delivery Complete!</h3>
    <p>Your rental items have been successfully delivered. We hope everything is to your satisfaction!</p>
    
    <div class="alert alert-success">
        <strong>Important reminders:</strong><br>
        • Inspect all items and report any issues within 24 hours<br>
        • Keep our contact information handy for support<br>
        • Remember your return date: {{ $returnDate ?? 'As specified in your contract' }}<br>
        • Enjoy your rental experience!
    </div>
</div>
@endif

@if($order->status === 'completed')
<div class="content-section">
    <h3>✅ Rental Complete - Thank You!</h3>
    <p>Your rental period has ended and we hope you had a fantastic experience with {{ $companyName }}!</p>
    
    <div class="text-center" style="margin: 30px 0;">
        <h4>⭐ How was your experience?</h4>
        <p>We'd love to hear your feedback!</p>
        <a href="#" class="btn btn-primary">Leave a Review</a>
    </div>
    
    <div class="alert alert-success">
        <strong>What's next?</strong><br>
        • Final invoice will be sent within 24 hours<br>
        • Any deposit refunds will be processed within 3-5 business days<br>
        • Book your next rental anytime with your account<br>
        • Contact us for future rental needs
    </div>
</div>
@endif

@if($order->status === 'cancelled')
<div class="content-section">
    <h3>❌ Order Cancelled</h3>
    <p>Your order has been cancelled as requested. We're sorry to see you go!</p>
    
    <div class="alert alert-warning">
        <strong>Refund Information:</strong><br>
        If you've already made a payment, we'll process your refund within 3-5 business days according to our refund policy.
    </div>
    
    <div class="text-center" style="margin: 30px 0;">
        <p>Need a different solution? We're here to help!</p>
        <a href="{{ $productsUrl ?? '#' }}" class="btn btn-primary">Browse Other Options</a>
    </div>
</div>
@endif

<!-- Contact Information -->
<div class="content-section">
    <h3>📞 Questions or Concerns?</h3>
    <p>Our team is here to help you every step of the way. Don't hesitate to reach out:</p>
    
    <div class="card">
        <table style="width: 100%; margin: 0;">
            <tr>
                <td style="padding: 8px 0;">
                    <strong>📧 Email:</strong> <a href="mailto:{{ $supportEmail }}">{{ $supportEmail }}</a>
                </td>
            </tr>
            <tr>
                <td style="padding: 8px 0;">
                    <strong>📱 Phone:</strong> <a href="tel:{{ $contactPhone }}">{{ $contactPhone }}</a>
                </td>
            </tr>
            <tr>
                <td style="padding: 8px 0;">
                    <strong>🕒 Support Hours:</strong> Monday - Friday, 8:00 AM - 8:00 PM
                </td>
            </tr>
        </table>
    </div>
</div>

<!-- Action Buttons -->
<div class="content-section text-center">
    <a href="{{ $orderDetailsUrl }}" class="btn btn-primary" style="margin: 10px;">
        📋 View Full Order Details
    </a>
    
    <a href="{{ $dashboardUrl }}" class="btn btn-secondary" style="margin: 10px;">
        🏠 Go to Dashboard
    </a>
</div>

<!-- Thank You -->
<div class="content-section text-center">
    <h3 style="color: var(--brand-deep-ash);">Thank You for Your Business! 🙏</h3>
    <p>We appreciate your trust in {{ $companyName }} for your kitchen rental needs.</p>
</div>
@endsection
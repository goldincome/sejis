@extends('emails.layouts.base')

@section('content')
<div class="greeting">
    Hello {{ $customerName }}! 👋
</div>

<div class="content-section">
    <h1>Order Confirmation</h1>
    <p>Thank you for your order! We're excited to help you with your kitchen rental needs. Your order has been received and is being processed.</p>
</div>

<!-- Order Summary Card -->
<div class="highlight-box">
    <h3 style="margin-top: 0;">📋 Order Summary</h3>
    <table style="width: 100%; margin: 0;">
        <tr>
            <td style="padding: 5px 0; font-weight: 600;">Order Reference:</td>
            <td style="padding: 5px 0; text-align: right;">{{ $order->order_reference }}</td>
        </tr>
        <tr>
            <td style="padding: 5px 0; font-weight: 600;">Order Date:</td>
            <td style="padding: 5px 0; text-align: right;">{{ $orderDate }} at {{ $orderTime }}</td>
        </tr>
        <tr>
            <td style="padding: 5px 0; font-weight: 600;">Total Amount:</td>
            <td style="padding: 5px 0; text-align: right; font-size: 18px; font-weight: 600; color: var(--brand-deep-ash);">£{{ number_format($orderTotal, 2) }}</td>
        </tr>
        @if($deliveryDate)
        <tr>
            <td style="padding: 5px 0; font-weight: 600;">Delivery Date:</td>
            <td style="padding: 5px 0; text-align: right;">{{ $deliveryDate }}</td>
        </tr>
        @endif
        @if($returnDate)
        <tr>
            <td style="padding: 5px 0; font-weight: 600;">Return Date:</td>
            <td style="padding: 5px 0; text-align: right;">{{ $returnDate }}</td>
        </tr>
        @endif
    </table>
</div>

<!-- Order Items -->
<div class="content-section">
    <h3>📦 Order Items</h3>
    <table class="table">
        <thead>
            <tr>
                <th>Item</th>
                <th style="text-align: center;">Quantity</th>
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

<!-- Payment Information -->
<div class="content-section">
    <h3>💳 Payment Information</h3>
    <div class="card">
        <table style="width: 100%; margin: 0;">
            <tr>
                <td style="padding: 8px 0; font-weight: 600;">Payment Method:</td>
                <td style="padding: 8px 0; text-align: right;">{{ $paymentMethod }}</td>
            </tr>
            <tr>
                <td style="padding: 8px 0; font-weight: 600;">Payment Status:</td>
                <td style="padding: 8px 0; text-align: right;">
                    @php
                        $statusColor = match($order->payment_status) {
                            'completed', 'paid' => 'success',
                            'pending' => 'warning',
                            'failed' => 'error',
                            default => 'secondary'
                        };
                    @endphp
                    <span class="badge badge-{{ $statusColor }}">{{ $paymentStatus }}</span>
                </td>
            </tr>
            <tr>
                <td style="padding: 8px 0; font-weight: 600;">Order Status:</td>
                <td style="padding: 8px 0; text-align: right;">
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
        </table>
    </div>
</div>

<!-- Next Steps -->
<div class="content-section">
    <h3>🚀 What Happens Next?</h3>
    <div class="alert alert-success">
        <strong>Your order is being processed!</strong><br>
        We'll send you another email confirmation once your order has been confirmed and is ready for delivery.
    </div>
    
    <ol style="line-height: 1.8; color: var(--text-secondary);">
        <li><strong>Order Review:</strong> Our team will review your order and confirm availability</li>
        <li><strong>Preparation:</strong> We'll prepare your rental items for delivery</li>
        <li><strong>Delivery Scheduling:</strong> We'll contact you to schedule the delivery</li>
        <li><strong>Setup & Support:</strong> Our team will assist with setup and provide ongoing support</li>
    </ol>
</div>

<!-- Contact Information -->
<div class="content-section">
    <h3>📞 Need Help?</h3>
    <p>If you have any questions about your order, please don't hesitate to contact us:</p>
    
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
                    <strong>🕒 Business Hours:</strong> Monday - Friday, 9:00 AM - 6:00 PM
                </td>
            </tr>
        </table>
    </div>
</div>

<!-- Action Buttons -->
<div class="content-section text-center">
    <a href="{{ $orderDetailsUrl }}" class="btn btn-primary" style="margin: 10px;">
        📋 View Order Details
    </a>
    
    <a href="{{ $dashboardUrl }}" class="btn btn-secondary" style="margin: 10px;">
        🏠 Go to Dashboard
    </a>
</div>

<!-- Important Notes -->
<div class="content-section">
    <div class="alert alert-warning">
        <strong>📝 Important Notes:</strong><br>
        • Please ensure someone is available at the delivery address during the scheduled time<br>
        • Have adequate space prepared for your rental items<br>
        • Review our terms and conditions for rental periods and returns<br>
        • Contact us immediately if you need to make any changes to your order
    </div>
</div>

<!-- Thank You -->
<div class="content-section text-center">
    <h3 style="color: var(--brand-deep-ash);">Thank You for Choosing {{ $companyName }}! 🙏</h3>
    <p>We're committed to providing you with the best kitchen rental experience possible. Your success is our success!</p>
</div>
@endsection
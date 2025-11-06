<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice #{{ $order->reference }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 12px;
            line-height: 1.6;
            color: #333;
        }
        
        .invoice-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .header {
            display: table;
            width: 100%;
            margin-bottom: 30px;
        }
        
        .header-left {
            display: table-cell;
            width: 50%;
            vertical-align: top;
        }
        
        .header-right {
            display: table-cell;
            width: 50%;
            vertical-align: top;
            text-align: right;
        }
        
        .company-logo {
            font-size: 24px;
            font-weight: bold;
            color: #2563eb;
            margin-bottom: 10px;
        }
        
        .company-details {
            font-size: 11px;
            color: #666;
        }
        
        .invoice-title {
            font-size: 28px;
            font-weight: bold;
            color: #1f2937;
            margin-bottom: 10px;
        }
        
        .invoice-meta {
            font-size: 11px;
            color: #666;
        }
        
        .billing-section {
            display: table;
            width: 100%;
            margin-bottom: 30px;
        }
        
        .bill-to {
            display: table-cell;
            width: 50%;
            vertical-align: top;
        }
        
        .order-info {
            display: table-cell;
            width: 50%;
            vertical-align: top;
            padding-left: 20px;
        }
        
        .section-title {
            font-size: 14px;
            font-weight: bold;
            color: #1f2937;
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .customer-info, .order-details {
            font-size: 11px;
            line-height: 1.8;
        }
        
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        
        .items-table th {
            background-color: #f3f4f6;
            padding: 12px 8px;
            text-align: left;
            font-weight: bold;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 2px solid #e5e7eb;
        }
        
        .items-table td {
            padding: 12px 8px;
            font-size: 11px;
            border-bottom: 1px solid #e5e7eb;
            vertical-align: top;
        }
        
        .items-table tr:nth-child(even) {
            background-color: #f9fafb;
        }
        
        .text-right {
            text-align: right;
        }
        
        .text-center {
            text-align: center;
        }
        
        .summary-table {
            width: 300px;
            margin-left: auto;
            border-collapse: collapse;
        }
        
        .summary-table td {
            padding: 8px 12px;
            font-size: 12px;
            border-bottom: 1px solid #e5e7eb;
        }
        
        .summary-table .total-row {
            font-weight: bold;
            font-size: 14px;
            background-color: #f3f4f6;
        }
        
        .payment-info {
            margin-top: 30px;
            padding: 20px;
            background-color: #f0f9ff;
            border-left: 4px solid #2563eb;
        }
        
        .payment-info .section-title {
            color: #1e40af;
            margin-bottom: 15px;
        }
        
        .payment-details {
            display: table;
            width: 100%;
        }
        
        .payment-left, .payment-right {
            display: table-cell;
            width: 50%;
            vertical-align: top;
            font-size: 11px;
        }
        
        .payment-right {
            padding-left: 20px;
        }
        
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 2px solid #e5e7eb;
            text-align: center;
            font-size: 10px;
            color: #6b7280;
        }
        
        .status-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .status-paid {
            background-color: #dcfce7;
            color: #166534;
        }
        
        .status-pending {
            background-color: #fef3c7;
            color: #92400e;
        }
        
        .booking-info {
            background-color: #eff6ff;
            padding: 8px;
            border-radius: 4px;
            margin-top: 5px;
            font-size: 10px;
        }
    </style>
</head>
<body>
    <div class="invoice-container">
        <!-- Header -->
        <div class="header">
            <div class="header-left">
                <div class="company-logo">{{ $company['name'] }}</div>
                <div class="company-details">
                    {{ $company['address'] }}<br>
                    Phone: {{ $company['phone'] }}<br>
                    Email: {{ $company['email'] }}
                </div>
            </div>
            <div class="header-right">
                <div class="invoice-title">INVOICE</div>
                <div class="invoice-meta">
                    <strong>Invoice #:</strong> {{ $order->reference }}<br>
                    <strong>Date:</strong> {{ $order->created_at->format('M d, Y') }}<br>
                    @if($order->paid_at)
                    <strong>Paid:</strong> {{ $order->paid_at->format('M d, Y') }}
                    @endif
                </div>
            </div>
        </div>
        
        <!-- Billing Information -->
        <div class="billing-section">
            <div class="bill-to">
                <div class="section-title">Bill To</div>
                <div class="customer-info">
                    <strong>{{ $order->user->name }}</strong><br>
                    {{ $order->user->email }}<br>
                    @if($order->user->phone)
                    Phone: {{ $order->user->phone }}<br>
                    @endif
                    @if($order->user->address)
                    {{ $order->user->address }}
                    @endif
                </div>
            </div>
            <div class="order-info">
                <div class="section-title">Order Information</div>
                <div class="order-details">
                    <strong>Order #:</strong> {{ $order->order_no }}<br>
                    <strong>Reference:</strong> {{ $order->reference }}<br>
                    <strong>Status:</strong> 
                    <span class="status-badge status-{{ $order->status === 'paid' ? 'paid' : 'pending' }}">
                        {{ ucfirst($order->status) }}
                    </span><br>
                    @if($order->payment_gateway)
                    <strong>Payment Method:</strong> {{ ucfirst($order->payment_gateway) }}<br>
                    @endif
                    @if($order->transaction_id)
                    <strong>Transaction ID:</strong> {{ $order->transaction_id }}
                    @endif
                </div>
            </div>
        </div>
        
        <!-- Order Items -->
        <table class="items-table">
            <thead>
                <tr>
                    <th style="width: 50%;">Description</th>
                    <th class="text-center" style="width: 10%;">Qty</th>
                    <th class="text-right" style="width: 20%;">Unit Price</th>
                    <th class="text-right" style="width: 20%;">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->orderDetails as $detail)
                <tr>
                    <td>
                        <strong>{{ $detail->name }}</strong>
                        @if($detail->booked_date)
                        <div class="booking-info">
                            <strong>📅 Booking Date:</strong> {{ \Carbon\Carbon::parse($detail->booked_date)->format('M d, Y') }}
                            @if($detail->booked_durations)
                                @php
                                    $duration = json_decode($detail->booked_durations, true);
                                @endphp
                                @if(isset($duration['time_slot']))
                                <br><strong>🕐 Time Slot:</strong> {{ $duration['time_slot'] }}
                                @endif
                            @endif
                        </div>
                        @endif
                    </td>
                    <td class="text-center">{{ $detail->quantity }}</td>
                    <td class="text-right">£{{ number_format($detail->price, 2) }}</td>
                    <td class="text-right">£{{ number_format($detail->sub_total, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        
        <!-- Order Summary -->
        <table class="summary-table">
            <tr>
                <td>Subtotal:</td>
                <td class="text-right">£{{ number_format($order->sub_total, 2) }}</td>
            </tr>
            @if($order->tax > 0)
            <tr>
                <td>Tax:</td>
                <td class="text-right">£{{ number_format($order->tax, 2) }}</td>
            </tr>
            @endif
            <tr class="total-row">
                <td>Total:</td>
                <td class="text-right">£{{ number_format($order->total, 2) }}</td>
            </tr>
        </table>
        
        <!-- Payment Information -->
        @if($order->payment_gateway)
        <div class="payment-info">
            <div class="section-title">Payment Information</div>
            <div class="payment-details">
                <div class="payment-left">
                    <strong>Payment Method:</strong> {{ ucfirst($order->payment_gateway) }}<br>
                    @if($order->transaction_id)
                    <strong>Transaction ID:</strong> {{ $order->transaction_id }}<br>
                    @endif
                    <strong>Payment Status:</strong> 
                    <span class="status-badge status-{{ $order->status === 'paid' ? 'paid' : 'pending' }}">
                        {{ $order->status === 'paid' ? 'Paid' : 'Pending' }}
                    </span>
                </div>
                <div class="payment-right">
                    @if($order->paid_at)
                    <strong>Payment Date:</strong> {{ $order->paid_at->format('M d, Y h:i A') }}<br>
                    @endif
                    @if($order->bankDeposit)
                    <strong>Bank Reference:</strong> {{ $order->bankDeposit->bank_reference }}<br>
                    <strong>Deposit Date:</strong> {{ $order->bankDeposit->deposit_date->format('M d, Y') }}
                    @endif
                </div>
            </div>
        </div>
        @endif
        
        <!-- Footer -->
        <div class="footer">
            <p>Thank you for your business with {{ $company['name'] }}!</p>
            <p>This is a computer-generated invoice. For any queries, please contact us at {{ $company['email'] }}</p>
            <p>Generated on {{ now()->format('M d, Y h:i A') }}</p>
        </div>
    </div>
</body>
</html>
<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Address;

class AdminOrderNotificationMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public Order $order;
    public string $notificationType;
    public string $companyName;
    public string $adminEmail;
    public ?string $additionalInfo;

    /**
     * Create a new message instance.
     */
    public function __construct(Order $order, string $notificationType, ?string $additionalInfo = null)
    {
        $this->order = $order;
        $this->notificationType = $notificationType;
        $this->additionalInfo = $additionalInfo;
        $this->companyName = setting('site_name', 'Sejis Kitchen Rental');
        $this->adminEmail = setting('admin_email', 'admin@sejiskitchenrental.com');
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $subject = $this->getSubjectByType($this->notificationType);
        
        return new Envelope(
            from: new Address('noreply@sejiskitchenrental.com', $this->companyName),
            subject: $subject,
            tags: ['admin-notification', "type-{$this->notificationType}"],
            metadata: [
                'order_id' => $this->order->id,
                'order_reference' => $this->order->order_reference,
                'customer_id' => $this->order->user_id,
                'notification_type' => $this->notificationType,
                'order_total' => $this->order->total_amount,
            ],
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.admin.order-notification',
            with: [
                'order' => $this->order,
                'customer' => $this->order->user,
                'orderDetails' => $this->order->orderDetails()->with('product')->get(),
                'notificationType' => $this->notificationType,
                'notificationTitle' => $this->getNotificationTitle($this->notificationType),
                'notificationMessage' => $this->getNotificationMessage($this->notificationType),
                'urgencyLevel' => $this->getUrgencyLevel($this->notificationType),
                'urgencyColor' => $this->getUrgencyColor($this->notificationType),
                'actionRequired' => $this->getActionRequired($this->notificationType),
                'additionalInfo' => $this->additionalInfo,
                'companyName' => $this->companyName,
                'orderTotal' => $this->order->total_amount,
                'orderDate' => $this->order->created_at->format('F j, Y g:i A'),
                'customerName' => $this->order->user->name ?? 'Unknown Customer',
                'customerEmail' => $this->order->user->email ?? 'Unknown Email',
                'customerPhone' => $this->order->user->phone ?? 'Not provided',
                'paymentMethod' => ucfirst($this->order->payment_method ?? 'Unknown'),
                'paymentStatus' => ucfirst($this->order->payment_status ?? 'pending'),
                'orderStatus' => ucfirst($this->order->status ?? 'pending'),
                'deliveryDate' => $this->order->rental_start_date ? $this->order->rental_start_date->format('F j, Y') : null,
                'returnDate' => $this->order->rental_end_date ? $this->order->rental_end_date->format('F j, Y') : null,
                'deliveryAddress' => $this->order->delivery_address ?? 'Not specified',
                'specialInstructions' => $this->order->special_instructions ?? 'None',
                'adminDashboardUrl' => route('admin.dashboard.index'),
                'orderManageUrl' => route('admin.orders.show', $this->order->id),
                'orderEditUrl' => route('admin.orders.edit', $this->order->id),
                'customerManageUrl' => route('admin.customers.show', $this->order->user_id),
                'systemStats' => $this->getSystemStats(),
            ]
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }

    /**
     * Get subject by notification type
     */
    private function getSubjectByType(string $type): string
    {
        return match($type) {
            'new_order' => "🆕 New Order Received - {$this->order->order_reference}",
            'payment_received' => "💰 Payment Received - {$this->order->order_reference}",
            'payment_failed' => "❌ Payment Failed - {$this->order->order_reference}",
            'high_value_order' => "🔥 High Value Order - {$this->order->order_reference} (£{$this->order->total_amount})",
            'cancellation_request' => "🚫 Cancellation Request - {$this->order->order_reference}",
            'refund_request' => "💸 Refund Request - {$this->order->order_reference}",
            'delivery_issue' => "⚠️ Delivery Issue - {$this->order->order_reference}",
            'customer_complaint' => "📞 Customer Complaint - {$this->order->order_reference}",
            'urgent_attention' => "🚨 Urgent Attention Required - {$this->order->order_reference}",
            default => "📋 Order Notification - {$this->order->order_reference}"
        };
    }

    /**
     * Get notification title
     */
    private function getNotificationTitle(string $type): string
    {
        return match($type) {
            'new_order' => 'New Order Received',
            'payment_received' => 'Payment Confirmed',
            'payment_failed' => 'Payment Failed',
            'high_value_order' => 'High Value Order Alert',
            'cancellation_request' => 'Cancellation Request',
            'refund_request' => 'Refund Request',
            'delivery_issue' => 'Delivery Issue Reported',
            'customer_complaint' => 'Customer Complaint',
            'urgent_attention' => 'Urgent Attention Required',
            default => 'Order Notification'
        };
    }

    /**
     * Get notification message
     */
    private function getNotificationMessage(string $type): string
    {
        return match($type) {
            'new_order' => 'A new order has been placed and requires your attention for processing.',
            'payment_received' => 'Payment has been successfully processed for this order.',
            'payment_failed' => 'Payment processing failed for this order. Customer may need assistance.',
            'high_value_order' => 'This is a high-value order that may require special attention or verification.',
            'cancellation_request' => 'Customer has requested to cancel this order. Please review and take appropriate action.',
            'refund_request' => 'Customer has requested a refund for this order. Please review the request.',
            'delivery_issue' => 'An issue has been reported with the delivery of this order.',
            'customer_complaint' => 'Customer has submitted a complaint regarding this order.',
            'urgent_attention' => 'This order requires urgent attention. Please review immediately.',
            default => 'This order requires your attention.'
        };
    }

    /**
     * Get urgency level
     */
    private function getUrgencyLevel(string $type): string
    {
        return match($type) {
            'urgent_attention', 'payment_failed', 'delivery_issue', 'customer_complaint' => 'HIGH',
            'high_value_order', 'cancellation_request', 'refund_request' => 'MEDIUM',
            'new_order', 'payment_received' => 'NORMAL',
            default => 'LOW'
        };
    }

    /**
     * Get urgency color
     */
    private function getUrgencyColor(string $type): string
    {
        return match($this->getUrgencyLevel($type)) {
            'HIGH' => 'red',
            'MEDIUM' => 'orange',
            'NORMAL' => 'blue',
            default => 'gray'
        };
    }

    /**
     * Get action required
     */
    private function getActionRequired(string $type): string
    {
        return match($type) {
            'new_order' => 'Review order details and confirm processing.',
            'payment_received' => 'Proceed with order fulfillment.',
            'payment_failed' => 'Contact customer to resolve payment issues.',
            'high_value_order' => 'Verify order details and customer information.',
            'cancellation_request' => 'Process cancellation and handle refund if applicable.',
            'refund_request' => 'Review refund request and process if approved.',
            'delivery_issue' => 'Investigate delivery issue and contact delivery team.',
            'customer_complaint' => 'Contact customer and resolve the complaint.',
            'urgent_attention' => 'Take immediate action as required.',
            default => 'Review order and take appropriate action.'
        };
    }

    /**
     * Get system statistics
     */
    private function getSystemStats(): array
    {
        return [
            'total_orders_today' => Order::whereDate('created_at', today())->count(),
            'pending_orders' => Order::where('status', 'pending')->count(),
            'total_revenue_today' => Order::whereDate('created_at', today())->sum('total_amount'),
            'high_value_orders_count' => Order::where('total_amount', '>', 1000)->whereDate('created_at', today())->count(),
        ];
    }
}

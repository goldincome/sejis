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

class OrderStatusUpdateMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public Order $order;
    public string $oldStatus;
    public string $newStatus;
    public string $customerName;
    public string $companyName;
    public string $supportEmail;
    public ?string $updateNote;

    /**
     * Create a new message instance.
     */
    public function __construct(Order $order, string $oldStatus, string $newStatus, ?string $updateNote = null)
    {
        $this->order = $order;
        $this->oldStatus = $oldStatus;
        $this->newStatus = $newStatus;
        $this->updateNote = $updateNote;
        $this->customerName = $order->user->name ?? 'Valued Customer';
        $this->companyName = setting('site_name', 'Sejis Kitchen Rental');
        $this->supportEmail = setting('contact_email', 'support@sejiskitchenrental.com');
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $statusDisplay = ucfirst(str_replace('_', ' ', $this->newStatus));
        
        return new Envelope(
            from: new Address($this->supportEmail, $this->companyName),
            subject: "Order Status Update - {$this->order->order_reference} ({$statusDisplay})",
            tags: ['order-status-update', "status-{$this->newStatus}"],
            metadata: [
                'order_id' => $this->order->id,
                'order_reference' => $this->order->order_reference,
                'customer_id' => $this->order->user_id,
                'old_status' => $this->oldStatus,
                'new_status' => $this->newStatus,
            ],
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.order.status-update',
            with: [
                'order' => $this->order,
                'customer' => $this->order->user,
                'oldStatus' => ucfirst(str_replace('_', ' ', $this->oldStatus)),
                'newStatus' => ucfirst(str_replace('_', ' ', $this->newStatus)),
                'statusBadgeColor' => $this->getStatusBadgeColor($this->newStatus),
                'statusIcon' => $this->getStatusIcon($this->newStatus),
                'statusMessage' => $this->getStatusMessage($this->newStatus),
                'updateNote' => $this->updateNote,
                'companyName' => $this->companyName,
                'supportEmail' => $this->supportEmail,
                'customerName' => $this->customerName,
                'orderTotal' => $this->order->total_amount,
                'orderDate' => $this->order->created_at->format('F j, Y'),
                'paymentMethod' => ucfirst($this->order->payment_method ?? 'Unknown'),
                'paymentStatus' => ucfirst($this->order->payment_status ?? 'pending'),
                'deliveryDate' => $this->order->rental_start_date ? $this->order->rental_start_date->format('F j, Y') : null,
                'returnDate' => $this->order->rental_end_date ? $this->order->rental_end_date->format('F j, Y') : null,
                'contactPhone' => setting('contact_phone', '+44 20 7946 0958'),
                'companyAddress' => setting('company_address', 'London, UK'),
                'websiteUrl' => config('app.url'),
                'dashboardUrl' => route('user.dashboard'),
                'orderDetailsUrl' => route('user.order.details', $this->order->order_reference),
                'trackingAvailable' => in_array($this->newStatus, ['confirmed', 'in_transit', 'delivered']),
                'showNextSteps' => $this->getNextSteps($this->newStatus),
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
     * Get status badge color based on status
     */
    private function getStatusBadgeColor(string $status): string
    {
        return match($status) {
            'pending' => 'yellow',
            'confirmed' => 'blue',
            'in_preparation' => 'indigo',
            'ready_for_delivery' => 'purple',
            'in_transit' => 'orange',
            'delivered' => 'green',
            'completed' => 'green',
            'cancelled' => 'red',
            'refunded' => 'gray',
            default => 'gray'
        };
    }

    /**
     * Get status icon based on status
     */
    private function getStatusIcon(string $status): string
    {
        return match($status) {
            'pending' => 'clock',
            'confirmed' => 'check-circle',
            'in_preparation' => 'cog',
            'ready_for_delivery' => 'box',
            'in_transit' => 'truck',
            'delivered' => 'home',
            'completed' => 'check-double',
            'cancelled' => 'times-circle',
            'refunded' => 'undo',
            default => 'info-circle'
        };
    }

    /**
     * Get status message based on status
     */
    private function getStatusMessage(string $status): string
    {
        return match($status) {
            'pending' => 'Your order is being reviewed and will be confirmed shortly.',
            'confirmed' => 'Great news! Your order has been confirmed and is now being prepared.',
            'in_preparation' => 'Your rental items are being prepared for delivery.',
            'ready_for_delivery' => 'Your order is ready and will be delivered soon.',
            'in_transit' => 'Your order is on its way to the delivery location.',
            'delivered' => 'Your order has been successfully delivered. Enjoy your rental!',
            'completed' => 'Your rental period has ended. Thank you for choosing us!',
            'cancelled' => 'Your order has been cancelled. If you have any questions, please contact us.',
            'refunded' => 'Your refund has been processed and will appear in your account shortly.',
            default => 'Your order status has been updated.'
        };
    }

    /**
     * Get next steps based on status
     */
    private function getNextSteps(string $status): ?string
    {
        return match($status) {
            'confirmed' => 'We will notify you when your order is ready for delivery.',
            'ready_for_delivery' => 'Please ensure someone is available at the delivery location.',
            'in_transit' => 'Track your delivery and prepare the setup location.',
            'delivered' => 'Inspect your rental items and contact us if there are any issues.',
            'completed' => 'We hope you had a great experience. Please consider leaving a review.',
            default => null
        };
    }
}

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

class OrderConfirmationMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public Order $order;
    public string $customerName;
    public string $companyName;
    public string $supportEmail;

    /**
     * Create a new message instance.
     */
    public function __construct(Order $order)
    {
        $this->order = $order;
        $this->customerName = $order->user->name ?? 'Valued Customer';
        $this->companyName = setting('site_name', 'Sejis Kitchen Rental');
        $this->supportEmail = setting('contact_email', 'support@sejiskitchenrental.com');
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address($this->supportEmail, $this->companyName),
            subject: "Order Confirmation - {$this->order->order_reference}",
            tags: ['order-confirmation'],
            metadata: [
                'order_id' => $this->order->id,
                'order_reference' => $this->order->order_reference,
                'customer_id' => $this->order->user_id,
            ],
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.order.confirmation',
            with: [
                'order' => $this->order,
                'customer' => $this->order->user,
                'orderDetails' => $this->order->orderDetails()->with('product')->get(),
                'companyName' => $this->companyName,
                'supportEmail' => $this->supportEmail,
                'customerName' => $this->customerName,
                'orderTotal' => $this->order->total_amount,
                'orderDate' => $this->order->created_at->format('F j, Y'),
                'orderTime' => $this->order->created_at->format('g:i A'),
                'paymentMethod' => ucfirst($this->order->payment_method ?? 'Unknown'),
                'paymentStatus' => ucfirst($this->order->payment_status ?? 'pending'),
                'orderStatus' => ucfirst($this->order->status ?? 'pending'),
                'deliveryDate' => $this->order->rental_start_date ? $this->order->rental_start_date->format('F j, Y') : null,
                'returnDate' => $this->order->rental_end_date ? $this->order->rental_end_date->format('F j, Y') : null,
                'contactPhone' => setting('contact_phone', '+44 20 7946 0958'),
                'companyAddress' => setting('company_address', 'London, UK'),
                'websiteUrl' => config('app.url'),
                'dashboardUrl' => route('user.dashboard'),
                'orderDetailsUrl' => route('user.order.details', $this->order->order_reference),
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
}

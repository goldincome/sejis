<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;
use Illuminate\Auth\Access\Response;
use Carbon\Carbon;

class OrderPolicy
{
    /**
     * Determine whether the user can view any orders.
     */
    public function viewAny(User $user): bool
    {
        // Users can view their own orders, admins can view all orders
        return $user->hasRole('admin') || $user->exists;
    }

    /**
     * Determine whether the user can view the order.
     */
    public function view(User $user, Order $order): bool
    {
        // Users can view their own orders, admins can view all orders
        return $user->hasRole('admin') || $order->user_id === $user->id;
    }

    /**
     * Determine whether the user can create orders.
     */
    public function create(User $user): bool
    {
        // Authenticated users can create orders
        return $user->exists;
    }

    /**
     * Determine whether the user can update the order.
     */
    public function update(User $user, Order $order): bool
    {
        // Only admins can update orders
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can delete the order.
     */
    public function delete(User $user, Order $order): bool
    {
        // Only admins can delete orders
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can restore the order.
     */
    public function restore(User $user, Order $order): bool
    {
        // Only admins can restore orders
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can permanently delete the order.
     */
    public function forceDelete(User $user, Order $order): bool
    {
        // Only admins can permanently delete orders
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can cancel the order.
     */
    public function cancel(User $user, Order $order): bool
    {
        // Users can cancel their own orders if conditions are met
        if ($order->user_id !== $user->id) {
            return false;
        }

        // Cannot cancel if order is already completed, delivered, or cancelled
        if (in_array($order->status, ['completed', 'delivered', 'cancelled', 'refunded'])) {
            return false;
        }

        // Check if within cancellation period (24 hours before booking)
        $earliestBookingDate = $order->orderDetails
            ->where('booked_date', '!=', null)
            ->min('booked_date');

        if ($earliestBookingDate && Carbon::parse($earliestBookingDate)->subHours(24)->isPast()) {
            return false;
        }

        return true;
    }

    /**
     * Determine whether the user can request a refund for the order.
     */
    public function requestRefund(User $user, Order $order): bool
    {
        // Users can request refund for their own paid orders
        if ($order->user_id !== $user->id) {
            return false;
        }

        // Can only request refund for paid orders
        if (!in_array($order->status, ['paid', 'completed', 'delivered'])) {
            return false;
        }

        // Cannot request refund if already refunded or refund requested
        if (in_array($order->status, ['refunded', 'refund_requested'])) {
            return false;
        }

        return true;
    }

    /**
     * Determine whether the user can download invoice for the order.
     */
    public function downloadInvoice(User $user, Order $order): bool
    {
        // Users can download invoice for their own paid orders
        if ($order->user_id !== $user->id) {
            return false;
        }

        // Invoice only available for paid orders
        return in_array($order->status, ['paid', 'completed', 'delivered']);
    }

    /**
     * Determine whether the user can track the order.
     */
    public function track(User $user, Order $order): bool
    {
        // Users can track their own orders, admins can track all orders
        return $user->hasRole('admin') || $order->user_id === $user->id;
    }

    /**
     * Determine whether the user can view order details.
     */
    public function viewDetails(User $user, Order $order): bool
    {
        // Same as view permission
        return $this->view($user, $order);
    }

    /**
     * Determine whether the user can leave a review for the order.
     */
    public function review(User $user, Order $order): bool
    {
        // Users can review their own completed orders
        if ($order->user_id !== $user->id) {
            return false;
        }

        // Can only review completed orders
        return in_array($order->status, ['completed', 'delivered']);
    }

    /**
     * Determine whether the user can reorder (create new order based on this one).
     */
    public function reorder(User $user, Order $order): bool
    {
        // Users can reorder their own orders
        return $order->user_id === $user->id;
    }

    /**
     * Determine whether the user can contact support about the order.
     */
    public function contactSupport(User $user, Order $order): bool
    {
        // Users can contact support about their own orders
        return $order->user_id === $user->id;
    }
}

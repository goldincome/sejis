<?php
namespace App\Services;

use App\Models\User;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Enums\PaymentStatusEnum;
use Gloudemans\Shoppingcart\Facades\Cart;

class OrderService
{

   public function createOrder(array $orderData): Order
    {   
        foreach(Cart::content() as $cartItem) { 
            $orderData['payment_method'] = $cartItem->options->payment_method;
;        }
        $order = Order::create([
            'user_id' => auth()->id(),
            'order_no' => $this->generateOrderNumber(),
            'payment_method' => $orderData['payment_method'],
            'total' => Cart::total(2, '.', ''),
            'sub_total' => Cart::subtotal(2, '.', ''),
            'tax' => Cart::tax(2, '.', ''),
            'currency' => config('cashier.currency'),
            'status' => PaymentStatusEnum::Pending->value,
        ]);
        
        //create order details
        $this->createOrderDetails($order);
        
        return $order;
    }


    protected function createOrderDetails(Order $order)
    {   
        foreach(Cart::content() as $index =>  $cartItem) {
            
            $orderDetail = OrderDetail::create([
                'name' => $cartItem->name,
                'ref_no' => $this->generateOrderNumber().$index,
                'order_id' => $order->id,
                'product_id' => $cartItem->options->product_model_id,
                'quantity' => $cartItem->qty,
                'price' => $cartItem->price,
                'sub_total' => $cartItem->subtotal,
                'booked_date' => $cartItem->options->booking_date ?? null,
                'booked_durations' => $cartItem->options->booking_time_raw ? getAllBookingTimeJson($cartItem->options->booking_date, $cartItem->options->booking_time_raw) : null,
            ]);
        }
    }

    protected function generateOrderNumber()
    {
        $number = mt_rand(1000000000, 9999999999); // better than rand()

        // call the same function if the number exists already
        if ($this->orderNumberExists($number)) {
            return $this->generateOrderNumber();
        }
        // otherwise, it's valid and can be used
        return $number;
    }

    protected function orderNumberExists($number)
    {
        return Order::where('order_no', $number)->exists();
    }

    public function getAllOrders(?User $user)
    {
        if($user){
            return $user->orders()->latest()->paginate();
        }
        return Order::with('user')->latest()->paginate(10);
    }

    public function updateOrder(Order $order, array $data): Order
    {
        $order->update($data);
        return $order;
    }

}
<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Models\Order;
use App\Enums\OrderStatusEnum;
use App\Services\OrderService;
use App\Http\Requests\OrderRequest;
use App\Http\Controllers\Controller;

class OrderController extends Controller
{
    protected $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $orders = $this->orderService->getAllOrders(null);
        return view('admin.orders.index', compact('orders'));
    }


    /**
     * Display the specified resource.
     */
    public function show(Order $order)
    {
        $order->load('user', 'orderDetails.product'); // Eager load relations
        return view('admin.orders.show', compact('order'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Order $order)
    {
        $users = User::all();
        $statuses = OrderStatusEnum::cases();
        return view('admin.orders.edit', compact('order', 'users', 'statuses'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(OrderRequest $request, Order $order)
    {
        $this->orderService->updateOrder($order, $request->validated());

        return redirect()->route('admin.orders.index')
                         ->with('success', 'Order updated successfully.');
    }

}

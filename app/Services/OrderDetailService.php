<?php

namespace App\Services;

use App\Models\OrderDetail;

class OrderDetailService
{
    /**
     * Get all order details, paginated.
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getAllOrderDetails()
    {
        return OrderDetail::with('order.user')->latest()->paginate(10);
    }

    /**
     * Create a new order detail.
     *
     * @param array $data
     * @return \App\Models\OrderDetail
     */
    public function createOrderDetail(array $data): OrderDetail
    {
        return OrderDetail::create($data);
    }

    /**
     * Update an existing order detail.
     *
     * @param \App\Models\OrderDetail $orderDetail
     * @param array $data
     * @return \App\Models\OrderDetail
     */
    public function updateOrderDetail(OrderDetail $orderDetail, array $data): OrderDetail
    {
        $orderDetail->update($data);
        return $orderDetail;
    }

}

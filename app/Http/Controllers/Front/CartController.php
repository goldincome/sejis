<?php

namespace App\Http\Controllers\Front;

use App\Models\Order;
use Illuminate\Http\Request;
use App\Services\CartService;
use App\Http\Controllers\Controller;

class CartController extends Controller
{
    protected $cartService;

    public function __construct(CartService $cartService)
    {
        $this->cartService = $cartService;
        
    }
     /**
     * Display the shopping cart.
     */
    public function index()
    {    
        $cartItems = $this->cartService::content();
        return view('front.cart.index', compact('cartItems'));
    }

    

    /**
     * Update cart item quantity (primarily for rooms).
     */
    public function update(Request $request)
    {
        $request->validate([
            'rowId' => 'required|string',
            'quantity' => 'required|integer|min:1',
        ]);

        $rowId = $request->input('rowId');
        $item = $this->cartService->get($rowId);

        if (!$item) {
            return redirect()->route('cart.index')->with('error', 'Item not found in cart.');
        }

        // Virtual addresses quantity cannot be updated.
        if (isset($item->options['type']) && $item->options['type'] === 'virtual_address') {
            return redirect()->route('cart.index')->with('warning', 'Virtual address plan quantity cannot be changed.');
        }

        $this->cartService->update($rowId, $request->quantity);
        return redirect()->route('cart.index')->with('success', 'Cart updated successfully.');
    }

    /**
     * Remove an item from the cart.
     */
    public function remove(Request $request)
    {
        $request->validate(['rowId' => 'required']); // Cart item ID
        $this->cartService::remove($request->rowId);
        return redirect()->route('cart.index')->with('success', 'Item removed from cart.');
    }

    /**
     * Clear the entire cart.
     */
    public function clear()
    {
        $this->cartService::destroy();
        return redirect()->route('cart.index')->with('success', 'Cart cleared successfully.');
    }

    
}

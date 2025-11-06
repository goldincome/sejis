<?php

namespace App\Http\Controllers\Front;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use App\Services\CartService;
use App\Enums\ProductTypeEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreKitchenRentalRequest;

class KitchenRentalController extends Controller
{
    public function index()
    {
        //$catSlug = ProductTypeEnum::KITCHEN_RENTAL->value;
        //$kitchen = Product::where('type', $catSlug)->first();
        $kitchen = Product::where('type', ProductTypeEnum::KITCHEN_RENTAL->value)->first();
        if($kitchen){
            $kitchen->load('category', 'media');
        }
         return view('front.kitchen-rental.index', compact('kitchen'));
    }

    public function store(StoreKitchenRentalRequest $request, CartService $cartService)
    {
        $product = Product::where('id', $request->product_id)->with('category')->first();
       
        try{
            $cartService->addToCart($product, $request->all());
        } catch (\Exception $e) {
            // Log::error('Error adding product to cart: ' . $e->getMessage()); // Good practice to log
            return redirect()->back()->with('error', 'Error adding product to cart. Please try again.'.$e->getMessage())->withInput();
        }
        return redirect()->route('cart.index')->with('success', $product->name . ' added to cart.');
    }
}

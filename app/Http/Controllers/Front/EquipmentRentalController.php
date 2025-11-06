<?php

namespace App\Http\Controllers\Front;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use App\Services\CartService;
use App\Enums\ProductTypeEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreKitchenRentalRequest;

class EquipmentRentalController extends Controller
{
    public function index()
    {
        $equipments = Product::where('type', ProductTypeEnum::ITEM_RENTAL->value)->get();
        $equipments->load('category', 'media');
         return view('front.equipment-rental.index', compact('equipments'));
    }

    public function show($slug)
    {
        $equipment = Product::where('type', ProductTypeEnum::ITEM_RENTAL->value)
            ->where('slug', $slug)->first();
        $equipment->load('category', 'media');
        
        return view('front.equipment-rental.show', compact('equipment'));
    }

    public function store(Request $request, CartService $cartService)
    {
        // Basic validation, can be moved to a FormRequest like StoreKitchenRentalRequest
        $validatedData = $request->validate([
            'product_id' => 'required|exists:products,id',
            'product_name' => 'required|string', // Good to have, though not strictly needed by service
            'price_per_day' => 'required|numeric',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'quantity' => 'required|integer|min:1',
            'rental_duration' => 'required|integer|min:1',
        ]);
       
        $product = Product::where('type', ProductTypeEnum::ITEM_RENTAL->value)
            ->where('id', $request->product_id)->with('category', 'media')->first();
       //dd($product, $request->all());
        try{
            $cartService->addToCart($product, $validatedData);
        } catch (\Exception $e) {
            // Log::error('Error adding product to cart: ' . $e->getMessage()); // Good practice to log
            return redirect()->back()->with('error', 'Error adding product to cart. Please try again.'.$e->getMessage())->withInput();
        }
        return redirect()->route('cart.index')->with('success', $product->name . ' added to cart.');
    }
}

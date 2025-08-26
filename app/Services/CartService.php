<?php
namespace App\Services;

use App\Models\{Product};
use Illuminate\Http\Request;
use App\Enums\ProductTypeEnum;

use App\Enums\PaymentMethodEnum;
use Gloudemans\Shoppingcart\Facades\Cart;

class CartService extends Cart
{
    

    public function addToCart(Product $product, array $cartData)
    {  
        $bookingDate =  $cartData['booking_date'];
        $cartItemId = 'prod_' . $product->id . '_' . strtotime($bookingDate);
         
        $existingItem = self::search(function ($cartItem, $rowId) use ($cartItemId, $bookingDate, $product) {
            return ($cartItem->id === $cartItemId &&
                $cartItem->options->booking_date === $bookingDate
                && $cartItem->options['product_model_id'] === $product->id
            );
        });
        //dd($cartData, $bookingDate,$existingItem->isEmpty());
        $cartData['bookingDate'] = $bookingDate;
        if ($existingItem->isNotEmpty()) {
           //dd('merge');
           $this->mergeSimilarItemsInCart($product, $cartData, $cartItemId);
           return true;
        }
        if ($existingItem->isEmpty()) {
            
            $this->addNewItemToCart($product, $cartData, $cartItemId);
            return true;
        }
        return false;
    }

    public function addNewItemToCart(Product $product, array $cartData, string $cartItemId)
    {   $bookingDate =  $cartData['booking_date'];
        $bookingTimes = $cartData['booking_time'];
        $qty = 0;
        foreach ($bookingTimes as $bookingTimeRaw) {
            $bookingTimeNew[] = $bookingTimeRaw;
            $bookingTimeNewDisplay[] = $this->formatBookingTime($bookingTimeRaw);
            ++$qty;
        }

        $this->prepareItemAndAddToCart(
            $product,
            $cartItemId,
            $qty,
            $bookingDate,
            $bookingTimeNew,
            $bookingTimeNewDisplay
        );
        return true;
    }

     public function mergeSimilarItemsInCart(Product $product, array $cartData, string $cartItemId)
    {
        $bookingDate = $cartData['bookingDate'];
        foreach (self::content() as $cartItem) {
            if (
                $cartItem->id === $cartItemId &&
                $cartItem->options['booking_date'] === $bookingDate
                && $cartItem->options['product_model_id'] === $product->id
            ) {
                $qty = 0;
                $bookingTimeNew = $bookingTimeNewDisplay = [];
                $bookingTimes = $cartData['booking_time'];
                foreach ($bookingTimes as $bookingTimeRaw) {
                    $bookingTimeNew[] = $bookingTimeRaw;
                    $bookingTimeNewDisplay[] = $this->formatBookingTime($bookingTimeRaw);
                    ++$qty;
                }
                
                $bookingTimeNew = array_unique(array_merge($cartItem->options->booking_time_raw, $bookingTimeNew));
                $bookingTimeNewDisplay = array_unique(array_merge($cartItem->options->booking_time_display, $bookingTimeNewDisplay));
                $quantity = count($bookingTimeNew);//$cartItem->qty + $qty;
                //remove item from cart
                self::remove($cartItem->rowId);
                //Add merged items to the cart
                $this->prepareItemAndAddToCart(
                    $product,
                    $cartItemId,
                    $quantity,
                    $bookingDate,
                    $bookingTimeNew,
                    $bookingTimeNewDisplay
                );
                return true;
            }
        }

    }

    public function prepareItemAndAddToCart(
        $product,
        $cartItemId,
        $qty,
        $bookingDate,
        $bookingTimeNew,
        $bookingTimeNewDisplay
    )
    {   $cartData = [];
        $cartData['quantity'] = $qty;
        $cartData['productName'] = $product->name;
        $cartData['productPrice'] =  $product->price;
        $cartData['productCategorySlug'] = $product->category->slug;
        $cartData['productId'] = $product->id;
        $cartData['bookingDate'] = $bookingDate;
        $cartData['bookingTimeNew'] = $bookingTimeNew;
        $cartData['bookingTimeNewDisplay'] = $bookingTimeNewDisplay;
        $cartData['productIntro'] = $product->intro;
        $cartData['productImageUrl'] = $product->primary_image->getUrl();
        //add item to cart
        $this->addItemToCart($cartData, $cartItemId);
    }

    protected function addItemToCart(array $cartData, string $cartItemId)
    {
        self::add(
            $cartItemId, // Unique ID for this booking slot
            $cartData['productName'],
            $cartData['quantity'], // Hours
            $cartData['productPrice'], // Price per hour
            0, // No weight
            [ // Options
                'category_slug' => $cartData['productCategorySlug'],
                'tax' => (config('cart.tax') / 100) * ($cartData['productPrice'] * $cartData['productPrice']),
                'product_model_id' => $cartData['productId'], // Original product model ID
                'payment_method' => '',
                'booking_date' => $cartData['bookingDate'],
                'booking_time_raw' => $cartData['bookingTimeNew'],
                'booking_time_display' => $cartData['bookingTimeNewDisplay'],
                'description' => $cartData['productIntro'],
                'image' => $cartData['productImageUrl'] ?: 'https://placehold.co/100x80/1e4ed8/ffffff?text=V-Office'//
            ]
        );
    }
    
    /**
     * Helper to format booking time for display.
     * Example input: "0900-1000" -> "9:00 AM - 10:00 AM"
     */
    private function formatBookingTime($timeSlot)
    {
        if (preg_match('/(\d{2})(\d{2})-(\d{2})(\d{2})/', $timeSlot, $matches)) {
            $startTime = \DateTime::createFromFormat('Hi', $matches[1] . $matches[2])->format('g:i A');
            $endTime = \DateTime::createFromFormat('Hi', $matches[3] . $matches[4])->format('g:i A');
            return $startTime . ' - ' . $endTime;
        }
        return $timeSlot; // Return original if not in expected format
    }

    public function getFromCartItem($paymentMethod, $cartItem): array
    {
        return [ 
            'tax' => $cartItem->options->tax,
            'product_model_id' => $cartItem->options->product_model_id,
            'payment_method' => $paymentMethod,
            'booking_date' => $cartItem->options->booking_date ?? null,
            'booking_time_raw' => $cartItem->options->booking_time_raw ?? null,
            'booking_time_display' => $cartItem->options->booking_time_display ?? null,
            'description' => $cartItem->options->description,
            'image' => $cartItem->options->image, 
        ];
    }

     public function getPaymentMethodFromCart()
    { 
        foreach (self::content() as $item) {
            if (in_array($item->options->payment_method, PaymentMethodEnum::toArray())) {
                    return $item->options->payment_method;
            }
        }
        return null;
    }
}

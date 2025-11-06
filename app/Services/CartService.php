<?php

namespace App\Services;

use App\Models\{Product};
use Illuminate\Http\Request;
use App\Enums\ProductTypeEnum; // Import the Enum
use App\Enums\PaymentMethodEnum;
use Gloudemans\Shoppingcart\Facades\Cart;

class CartService extends Cart
{
    /**
     * Main "router" function to add a product to the cart.
     * It checks the product type and calls the appropriate method.
     */
    public function addToCart(Product $product, array $cartData)
    {
        if ($product->type->value == ProductTypeEnum::KITCHEN_RENTAL->value) {
            return $this->addKitchenToCart($product, $cartData);
        } elseif ($product->type->value == ProductTypeEnum::ITEM_RENTAL->value) {
            return $this->addEquipmentToCart($product, $cartData);
        }

        // Fallback in case product type is unknown
        return false;
    }

    // ======================================================================
    // KITCHEN RENTAL LOGIC (Refactored from original)
    // ======================================================================

    /**
     * Handles adding a KITCHEN rental to the cart.
     */
    protected function addKitchenToCart(Product $product, array $cartData)
    {
        $bookingDate = $cartData['booking_date'];
        $cartItemId = 'prod_' . $product->id . '_' . strtotime($bookingDate);

        $existingItem = self::search(function ($cartItem, $rowId) use ($cartItemId, $bookingDate, $product) {
            return ($cartItem->id === $cartItemId &&
                $cartItem->options->booking_date === $bookingDate
                && $cartItem->options['product_model_id'] === $product->id
            );
        });

        $cartData['bookingDate'] = $bookingDate;
        if ($existingItem->isNotEmpty()) {
            $this->mergeSimilarKitchenItemsInCart($product, $cartData, $cartItemId);
            return true;
        }
        if ($existingItem->isEmpty()) {
            $this->addNewKitchenItemToCart($product, $cartData, $cartItemId);
            return true;
        }
        return false;
    }

    /**
     * Adds a new kitchen item to the cart.
     */
    protected function addNewKitchenItemToCart(Product $product, array $cartData, string $cartItemId)
    {
        $bookingDate = $cartData['booking_date'];
        $bookingTimes = $cartData['booking_time'];
        $qty = 0;
        $bookingTimeNew = [];
        $bookingTimeNewDisplay = [];
        foreach ($bookingTimes as $bookingTimeRaw) {
            $bookingTimeNew[] = $bookingTimeRaw;
            $bookingTimeNewDisplay[] = $this->formatBookingTime($bookingTimeRaw);
            ++$qty;
        }

        $this->prepareKitchenItemAndAddToCart(
            $product,
            $cartItemId,
            $qty,
            $bookingDate,
            $bookingTimeNew,
            $bookingTimeNewDisplay
        );
        return true;
    }

    /**
     * Merges kitchen item time slots if the same product is added on the same day.
     */
    protected function mergeSimilarKitchenItemsInCart(Product $product, array $cartData, string $cartItemId)
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
                $quantity = count($bookingTimeNew);
                //remove item from cart
                self::remove($cartItem->rowId);
                //Add merged items to the cart
                $this->prepareKitchenItemAndAddToCart(
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

    /**
     * Prepares kitchen item data for the cart.
     */
    protected function prepareKitchenItemAndAddToCart(
        $product,
        $cartItemId,
        $qty,
        $bookingDate,
        $bookingTimeNew,
        $bookingTimeNewDisplay
    ) {
        $cartData = [];
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
        $this->addKitchenItemToCart($cartData, $cartItemId);
    }

    /**
     * Adds the final kitchen item to the cart session.
     */
    protected function addKitchenItemToCart(array $cartData, string $cartItemId)
    {
        self::add(
            $cartItemId, // Unique ID for this booking slot
            $cartData['productName'],
            $cartData['quantity'], // Hours
            $cartData['productPrice'], // Price per hour
            0, // No weight
            [ // Options
                'category_slug' => $cartData['productCategorySlug'],
                // *** FIXED: Tax calculation should be price * quantity, not price * price
                'tax' => (config('cart.tax') / 100) * ($cartData['productPrice'] * $cartData['quantity']),
                'product_model_id' => $cartData['productId'], // Original product model ID
                'payment_method' => '',
                'booking_date' => $cartData['bookingDate'],
                'booking_time_raw' => $cartData['bookingTimeNew'],
                'booking_time_display' => $cartData['bookingTimeNewDisplay'],
                'description' => $cartData['productIntro'],
                'image' => $cartData['productImageUrl'] ?: 'https://placehold.co/100x80/1e4ed8/ffffff?text=V-Office',
                'product_type' => ProductTypeEnum::KITCHEN_RENTAL->value // *** ADDED
            ]
        );
    }

    // ======================================================================
    // EQUIPMENT RENTAL LOGIC (New)
    // ======================================================================

    /**
     * Handles adding an EQUIPMENT rental to the cart.
     */
    protected function addEquipmentToCart(Product $product, array $cartData)
    {
        // Equipment is unique by product ID, start date, and end date
        $cartItemId = 'equip_' . $product->id . '_' . $cartData['start_date'] . '_' . $cartData['end_date'];

        $existingItem = self::search(function ($cartItem, $rowId) use ($cartItemId) {
            return $cartItem->id === $cartItemId;
        });

        if ($existingItem->isNotEmpty()) {
            // Item with same dates exists, merge quantities
            return $this->mergeSimilarEquipmentInCart($existingItem->first(), $cartData);
        } else {
            // New item or new dates, add as new cart line
            return $this->addNewEquipmentItemToCart($product, $cartItemId, $cartData);
        }
    }

    /**
     * Adds a new equipment item to the cart.
     */
    protected function addNewEquipmentItemToCart(Product $product, string $cartItemId, array $cartData)
    {
        $quantity = (int)$cartData['quantity']; // How many units (e.g., 2 mixers)
        $rental_duration = (int)$cartData['rental_duration']; // How many days
        $price_per_day = $product->price_per_day;
        // Price for one item for the full duration
        $total_price_per_item = $price_per_day * $rental_duration;
        $start_date = $cartData['start_date'];
        $end_date = $cartData['end_date'];

        self::add(
            $cartItemId, // Unique ID for this product on this date range
            $product->name,
            $quantity, // Number of items
            $total_price_per_item, // Price PER ITEM for the *entire duration*
            0, // No weight
            [ // Options
                'category_slug' => $product->category->slug,
                'tax' => (config('cart.tax') / 100) * ($total_price_per_item * $quantity),
                'product_model_id' => $product->id,
                'payment_method' => '',
                'start_date' => $start_date,
                'end_date' => $end_date,
                'rental_duration' => $rental_duration,
                'price_per_day' => $price_per_day,
                'description' => $product->intro,
                'image' => $product->primary_image->getUrl() ?: 'https://placehold.co/100x80/1e4ed8/ffffff?text=V-Office',
                'product_type' => ProductTypeEnum::ITEM_RENTAL->value
            ]
        );

        return true;
    }

    /**
     * Merges equipment item quantities if the same product is added on the same dates.
     */
    protected function mergeSimilarEquipmentInCart($existingItem, array $cartData)
    {
        $oldQty = $existingItem->qty;
        $qtyToAdd = (int)$cartData['quantity'];
        $newTotalQty = $oldQty + $qtyToAdd;

        // Price is per item for the full duration, so it doesn't change
        $price_per_item_duration = $existingItem->price;

        $newOptions = $existingItem->options;
        // Recalculate tax based on new total quantity
        $newOptions['tax'] = (config('cart.tax') / 100) * ($price_per_item_duration * $newTotalQty);

        self::update($existingItem->rowId, [
            'qty' => $newTotalQty,
            'options' => $newOptions
        ]);

        return true;
    }

    // ======================================================================
    // SHARED HELPER FUNCTIONS
    // ======================================================================

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

    /**
     * Gets item data from cart, now sensitive to product type.
     */
    public function getFromCartItem($paymentMethod, $cartItem): array
    {
        // Base data
        $data = [
            'tax' => $cartItem->options->tax,
            'product_model_id' => $cartItem->options->product_model_id,
            'payment_method' => $paymentMethod,
            'description' => $cartItem->options->description,
            'image' => $cartItem->options->image,
            'product_type' => $cartItem->options->product_type ?? null
        ];

        // Add type-specific data
        if ($cartItem->options->product_type == ProductTypeEnum::KITCHEN_RENTAL->value) {
            $data['booking_date'] = $cartItem->options->booking_date ?? null;
            $data['booking_time_raw'] = $cartItem->options->booking_time_raw ?? null;
            $data['booking_time_display'] = $cartItem->options->booking_time_display ?? null;
        } elseif ($cartItem->options->product_type == ProductTypeEnum::ITEM_RENTAL->value) {
            $data['start_date'] = $cartItem->options->start_date ?? null;
            $data['end_date'] = $cartItem->options->end_date ?? null;
            $data['rental_duration'] = $cartItem->options->rental_duration ?? null;
            $data['price_per_day'] = $cartItem->options->price_per_day ?? null;
        }

        return $data;
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
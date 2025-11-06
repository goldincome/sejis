<?php

namespace App\Exceptions;

use Exception;
use Throwable;

class CartException extends Exception
{
    public function __construct(
        string $message = 'Cart operation failed',
        public readonly ?string $operation = null,
        public readonly ?array $context = null,
        int $code = 0,
        ?Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
    
    /**
     * Get additional context for logging
     */
    public function context(): array
    {
        return array_merge([
            'operation' => $this->operation,
            'user_id' => auth()->id(),
            'cart_content' => session()->get('cart'),
            'ip_address' => request()->ip(),
        ], $this->context ?? []);
    }
    
    /**
     * Report the exception for logging
     */
    public function report(): void
    {
        logger()->warning('Cart Exception: ' . $this->getMessage(), $this->context());
    }
    
    /**
     * Render the exception as an HTTP response
     */
    public function render($request)
    {
        if ($request->expectsJson()) {
            return response()->json([
                'error' => 'Cart operation failed',
                'message' => $this->getMessage(),
                'operation' => $this->operation
            ], 422);
        }
        
        return redirect()->route('cart.index')
            ->with('error', $this->getMessage())
            ->withInput();
    }
    
    /**
     * Static factory methods for common cart errors
     */
    public static function itemNotFound(string $itemId): self
    {
        return new self(
            message: "Item with ID '{$itemId}' not found in cart",
            operation: 'item_lookup',
            context: ['item_id' => $itemId]
        );
    }
    
    public static function insufficientStock(string $productName, int $requested, int $available): self
    {
        return new self(
            message: "Insufficient stock for '{$productName}'. Requested: {$requested}, Available: {$available}",
            operation: 'stock_check',
            context: [
                'product_name' => $productName,
                'requested_quantity' => $requested,
                'available_quantity' => $available
            ]
        );
    }
    
    public static function invalidQuantity(int $quantity): self
    {
        return new self(
            message: "Invalid quantity: {$quantity}. Quantity must be positive.",
            operation: 'quantity_validation',
            context: ['invalid_quantity' => $quantity]
        );
    }
    
    public static function cartEmpty(): self
    {
        return new self(
            message: "Cart is empty. Please add items before proceeding.",
            operation: 'cart_validation'
        );
    }
}
<?php

namespace App\Exceptions;

use Exception;
use Throwable;

class PaymentException extends Exception
{
    public function __construct(
        string $message = 'Payment processing failed',
        public readonly ?string $gateway = null,
        public readonly ?string $transactionId = null,
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
        return [
            'gateway' => $this->gateway,
            'transaction_id' => $this->transactionId,
            'user_id' => auth()->id(),
            'ip_address' => request()->ip(),
        ];
    }
    
    /**
     * Report the exception for logging
     */
    public function report(): void
    {
        logger()->error('Payment Exception: ' . $this->getMessage(), $this->context());
    }
    
    /**
     * Render the exception as an HTTP response
     */
    public function render($request)
    {
        if ($request->expectsJson()) {
            return response()->json([
                'error' => 'Payment failed',
                'message' => $this->getMessage(),
                'gateway' => $this->gateway
            ], 422);
        }
        
        return redirect()->back()
            ->with('error', 'Payment failed: ' . $this->getMessage())
            ->withInput();
    }
}
<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\WebhookLog;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class TakePaymentsWebhookController extends Controller
{
    public function handle(Request $request): JsonResponse
    {
        try {
            // Validate webhook signature
            $this->validateSignature($request);
            
            // Check for duplicate processing
            $transactionUnique = $request->input('transactionUnique');
            if ($this->isDuplicateEvent($transactionUnique)) {
                return response()->json(['message' => 'Webhook already processed'], 200);
            }
            
            // Log webhook receipt
            $this->logWebhook('takepayments', $request->all(), 'received');
            
            $responseCode = $request->input('responseCode');
            $orderRef = $request->input('orderRef');
            
            if ($responseCode === '0') {
                $this->handlePaymentSuccess($request);
            } else {
                $this->handlePaymentFailure($request);
            }
            
            // Mark event as processed
            $this->markEventProcessed($transactionUnique);
            
            return response()->json(['status' => 'success'], 200);
            
        } catch (\Exception $e) {
            Log::warning('Webhook security violation', [
                'gateway' => 'takepayments',
                'ip' => $request->ip(),
                'reason' => $e->getMessage(),
                'payload_id' => $request->input('transactionUnique')
            ]);
            
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }
    
    protected function validateSignature(Request $request): void
    {
        $signature = $request->input('signature');
        $accessKey = config('takepayment.access_key');
        
        if (!$signature || !$accessKey) {
            throw new \Exception('Missing signature or access key');
        }
        
        // Remove signature from payload for hash calculation
        $payload = $request->except(['signature']);
        
        // Build query string for hash calculation
        $hashString = http_build_query($payload, '', '&');
        $expectedHash = hash('sha512', $hashString . $accessKey);
        
        if (!hash_equals($expectedHash, $signature)) {
            throw new \Exception('Invalid signature');
        }
    }
    
    protected function handlePaymentSuccess(Request $request): void
    {
        $orderRef = $request->input('orderRef');
        $transactionId = $request->input('transactionID');
        $amountReceived = $request->input('amountReceived'); // In pence
        
        if (!$orderRef) {
            Log::warning('TakePayments webhook missing order reference', $request->all());
            return;
        }
        
        $order = Order::where('reference', $orderRef)->first();
        if (!$order) {
            Log::warning('TakePayments webhook order not found', ['reference' => $orderRef]);
            return;
        }
        
        if ($order->status !== 'pending') {
            Log::info('TakePayments webhook order already processed', ['reference' => $orderRef]);
            return;
        }
        
        // Convert pence to pounds for validation
        $amountInPounds = $amountReceived / 100;
        
        // Validate amount matches order total
        if (abs($amountInPounds - $order->total) > 0.01) {
            Log::warning('TakePayments webhook amount mismatch', [
                'order_reference' => $orderRef,
                'expected' => $order->total,
                'received' => $amountInPounds
            ]);
            return;
        }
        
        $order->update([
            'status' => 'paid',
            'payment_gateway' => 'takepayments',
            'transaction_id' => $transactionId,
            'paid_at' => now()
        ]);
        
        Log::info('TakePayments payment confirmed', [
            'order_reference' => $orderRef,
            'transaction_id' => $transactionId,
            'amount' => $amountInPounds
        ]);
    }
    
    protected function handlePaymentFailure(Request $request): void
    {
        $orderRef = $request->input('orderRef');
        $responseCode = $request->input('responseCode');
        $responseMessage = $request->input('responseMessage');
        
        if ($orderRef) {
            $order = Order::where('reference', $orderRef)->first();
            if ($order) {
                $order->update([
                    'status' => 'failed',
                    'failure_reason' => "Code: {$responseCode}, Message: {$responseMessage}"
                ]);
            }
        }
        
        Log::warning('TakePayments payment failed', [
            'order_reference' => $orderRef,
            'response_code' => $responseCode,
            'response_message' => $responseMessage
        ]);
    }
    
    protected function isDuplicateEvent(string $transactionUnique): bool
    {
        return Cache::has("webhook_processed:takepayments:{$transactionUnique}");
    }
    
    protected function markEventProcessed(string $transactionUnique): void
    {
        // Store for 24 hours to prevent duplicate processing
        Cache::put("webhook_processed:takepayments:{$transactionUnique}", true, 86400);
    }
    
    protected function logWebhook(string $gateway, array $payload, string $status): void
    {
        WebhookLog::create([
            'gateway' => $gateway,
            'event_id' => $payload['transactionUnique'] ?? null,
            'event_type' => 'payment_notification',
            'payload' => $payload,
            'status' => $status,
            'processed_at' => now()
        ]);
    }
}
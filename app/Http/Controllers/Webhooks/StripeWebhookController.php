<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\WebhookLog;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class StripeWebhookController extends Controller
{
    public function handle(Request $request): JsonResponse
    {
        try {
            // Validate webhook signature
            $this->validateSignature($request);
            
            // Check for duplicate processing
            $eventId = $request->input('id');
            if ($this->isDuplicateEvent($eventId)) {
                return response()->json(['message' => 'Webhook already processed'], 200);
            }
            
            // Log webhook receipt
            $this->logWebhook('stripe', $request->all(), 'received');
            
            $eventType = $request->input('type');
            $data = $request->input('data.object', []);
            
            switch ($eventType) {
                case 'payment_intent.succeeded':
                    $this->handlePaymentSuccess($data);
                    break;
                    
                case 'payment_intent.payment_failed':
                    $this->handlePaymentFailure($data);
                    break;
                    
                case 'payment_intent.requires_action':
                    $this->handlePaymentRequiresAction($data);
                    break;
                    
                default:
                    Log::info('Unhandled Stripe webhook event', ['type' => $eventType]);
            }
            
            // Mark event as processed
            $this->markEventProcessed($eventId);
            
            return response()->json(['status' => 'success'], 200);
            
        } catch (\Exception $e) {
            Log::warning('Webhook security violation', [
                'gateway' => 'stripe',
                'ip' => $request->ip(),
                'reason' => $e->getMessage(),
                'payload_id' => $request->input('id')
            ]);
            
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }
    
    protected function validateSignature(Request $request): void
    {
        $signature = $request->header('Stripe-Signature');
        $payload = $request->getContent();
        $secret = config('stripe.webhook_secret');
        
        if (!$signature || !$secret) {
            throw new \Exception('Missing signature or webhook secret');
        }
        
        // Parse signature header
        $elements = explode(',', $signature);
        $timestamp = null;
        $signatures = [];
        
        foreach ($elements as $element) {
            $parts = explode('=', $element, 2);
            if (count($parts) === 2) {
                if ($parts[0] === 't') {
                    $timestamp = $parts[1];
                } elseif ($parts[0] === 'v1') {
                    $signatures[] = $parts[1];
                }
            }
        }
        
        if (!$timestamp || empty($signatures)) {
            throw new \Exception('Invalid signature format');
        }
        
        // Check timestamp tolerance (5 minutes)
        if (abs(time() - $timestamp) > 300) {
            throw new \Exception('Webhook timestamp too old');
        }
        
        // Verify signature
        $expectedSignature = hash_hmac('sha256', $timestamp . '.' . $payload, $secret);
        
        $isValid = false;
        foreach ($signatures as $signature) {
            if (hash_equals($expectedSignature, $signature)) {
                $isValid = true;
                break;
            }
        }
        
        if (!$isValid) {
            throw new \Exception('Invalid signature');
        }
    }
    
    protected function handlePaymentSuccess(array $data): void
    {
        $orderId = $data['metadata']['order_id'] ?? null;
        
        if (!$orderId) {
            Log::warning('Stripe webhook missing order_id in metadata', $data);
            return;
        }
        
        $order = Order::find($orderId);
        if (!$order) {
            Log::warning('Stripe webhook order not found', ['order_id' => $orderId]);
            return;
        }
        
        if ($order->status !== 'pending') {
            Log::info('Stripe webhook order already processed', ['order_id' => $orderId]);
            return;
        }
        
        $order->update([
            'status' => 'paid',
            'payment_gateway' => 'stripe',
            'transaction_id' => $data['id'],
            'paid_at' => now()
        ]);
        
        Log::info('Stripe payment confirmed', [
            'order_id' => $orderId,
            'transaction_id' => $data['id']
        ]);
    }
    
    protected function handlePaymentFailure(array $data): void
    {
        $orderId = $data['metadata']['order_id'] ?? null;
        
        if ($orderId) {
            $order = Order::find($orderId);
            if ($order) {
                $order->update([
                    'status' => 'failed',
                    'failure_reason' => $data['last_payment_error']['message'] ?? 'Payment failed'
                ]);
            }
        }
        
        Log::warning('Stripe payment failed', [
            'order_id' => $orderId,
            'error' => $data['last_payment_error']['message'] ?? 'Unknown error'
        ]);
    }
    
    protected function handlePaymentRequiresAction(array $data): void
    {
        $orderId = $data['metadata']['order_id'] ?? null;
        
        if ($orderId) {
            $order = Order::find($orderId);
            if ($order) {
                $order->update(['status' => 'requires_action']);
            }
        }
        
        Log::info('Stripe payment requires action', [
            'order_id' => $orderId,
            'payment_intent' => $data['id']
        ]);
    }
    
    protected function isDuplicateEvent(string $eventId): bool
    {
        return Cache::has("webhook_processed:stripe:{$eventId}");
    }
    
    protected function markEventProcessed(string $eventId): void
    {
        // Store for 24 hours to prevent duplicate processing
        Cache::put("webhook_processed:stripe:{$eventId}", true, 86400);
    }
    
    protected function logWebhook(string $gateway, array $payload, string $status): void
    {
        WebhookLog::create([
            'gateway' => $gateway,
            'event_id' => $payload['id'] ?? null,
            'event_type' => $payload['type'] ?? null,
            'payload' => $payload,
            'status' => $status,
            'processed_at' => now()
        ]);
    }
}
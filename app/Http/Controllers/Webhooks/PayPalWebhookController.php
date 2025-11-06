<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\WebhookLog;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class PayPalWebhookController extends Controller
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
            $this->logWebhook('paypal', $request->all(), 'received');
            
            $eventType = $request->input('event_type');
            $resource = $request->input('resource', []);
            
            switch ($eventType) {
                case 'PAYMENT.CAPTURE.COMPLETED':
                    $this->handlePaymentCaptured($resource);
                    break;
                    
                case 'PAYMENT.CAPTURE.DENIED':
                case 'PAYMENT.CAPTURE.DECLINED':
                    $this->handlePaymentFailed($resource);
                    break;
                    
                case 'PAYMENT.CAPTURE.PENDING':
                    $this->handlePaymentPending($resource);
                    break;
                    
                case 'PAYMENT.CAPTURE.REFUNDED':
                    $this->handlePaymentRefunded($resource);
                    break;
                    
                default:
                    Log::info('Unhandled PayPal webhook event', ['type' => $eventType]);
            }
            
            // Mark event as processed
            $this->markEventProcessed($eventId);
            
            return response()->json(['status' => 'success'], 200);
            
        } catch (\Exception $e) {
            Log::warning('Webhook security violation', [
                'gateway' => 'paypal',
                'ip' => $request->ip(),
                'reason' => $e->getMessage(),
                'payload_id' => $request->input('id')
            ]);
            
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }
    
    protected function validateSignature(Request $request): void
    {
        $transmissionId = $request->header('PAYPAL-TRANSMISSION-ID');
        $certId = $request->header('PAYPAL-CERT-ID');
        $transmissionTime = $request->header('PAYPAL-TRANSMISSION-TIME');
        $signature = $request->header('PAYPAL-TRANSMISSION-SIG');
        
        if (!$transmissionId || !$certId || !$transmissionTime || !$signature) {
            throw new \Exception('Missing required PayPal webhook headers');
        }
        
        // Verify with PayPal API
        $isValid = $this->verifyWithPayPal($request, [
            'transmission_id' => $transmissionId,
            'cert_id' => $certId,
            'transmission_time' => $transmissionTime,
            'webhook_signature' => $signature
        ]);
        
        if (!$isValid) {
            throw new \Exception('Invalid PayPal webhook signature');
        }
    }
    
    protected function verifyWithPayPal(Request $request, array $headers): bool
    {
        try {
            $webhookId = config('paypal.webhook_id');
            $clientId = config('paypal.client_id');
            $clientSecret = config('paypal.client_secret');
            
            if (!$webhookId || !$clientId || !$clientSecret) {
                throw new \Exception('PayPal webhook configuration missing');
            }
            
            // Get access token
            $tokenResponse = Http::withBasicAuth($clientId, $clientSecret)
                ->asForm()
                ->post(config('paypal.base_url') . '/v1/oauth2/token', [
                    'grant_type' => 'client_credentials'
                ]);
            
            if (!$tokenResponse->successful()) {
                throw new \Exception('Failed to get PayPal access token');
            }
            
            $accessToken = $tokenResponse->json('access_token');
            
            // Verify webhook signature
            $verifyResponse = Http::withToken($accessToken)
                ->post(config('paypal.base_url') . '/v1/notifications/verify-webhook-signature', [
                    'transmission_id' => $headers['transmission_id'],
                    'cert_id' => $headers['cert_id'],
                    'auth_algo' => 'SHA256withRSA',
                    'transmission_time' => $headers['transmission_time'],
                    'webhook_id' => $webhookId,
                    'webhook_event' => $request->all()
                ]);
            
            if (!$verifyResponse->successful()) {
                throw new \Exception('PayPal webhook verification failed');
            }
            
            $verificationStatus = $verifyResponse->json('verification_status');
            return $verificationStatus === 'SUCCESS';
            
        } catch (\Exception $e) {
            Log::error('PayPal webhook verification error', [
                'error' => $e->getMessage(),
                'headers' => $headers
            ]);
            return false;
        }
    }
    
    protected function handlePaymentCaptured(array $resource): void
    {
        $customId = $resource['custom_id'] ?? null;
        
        if (!$customId) {
            Log::warning('PayPal webhook missing custom_id (order reference)', $resource);
            return;
        }
        
        $order = Order::where('reference', $customId)->first();
        if (!$order) {
            Log::warning('PayPal webhook order not found', ['reference' => $customId]);
            return;
        }
        
        if ($order->status !== 'pending') {
            Log::info('PayPal webhook order already processed', ['reference' => $customId]);
            return;
        }
        
        $order->update([
            'status' => 'paid',
            'payment_gateway' => 'paypal',
            'transaction_id' => $resource['id'],
            'paid_at' => now()
        ]);
        
        Log::info('PayPal payment confirmed', [
            'order_reference' => $customId,
            'transaction_id' => $resource['id']
        ]);
    }
    
    protected function handlePaymentFailed(array $resource): void
    {
        $customId = $resource['custom_id'] ?? null;
        
        if ($customId) {
            $order = Order::where('reference', $customId)->first();
            if ($order) {
                $order->update([
                    'status' => 'failed',
                    'failure_reason' => $resource['reason_code'] ?? 'Payment failed'
                ]);
            }
        }
        
        Log::warning('PayPal payment failed', [
            'order_reference' => $customId,
            'reason' => $resource['reason_code'] ?? 'Unknown error'
        ]);
    }
    
    protected function handlePaymentPending(array $resource): void
    {
        $customId = $resource['custom_id'] ?? null;
        
        if ($customId) {
            $order = Order::where('reference', $customId)->first();
            if ($order) {
                $order->update(['status' => 'processing']);
            }
        }
        
        Log::info('PayPal payment pending', [
            'order_reference' => $customId,
            'capture_id' => $resource['id']
        ]);
    }
    
    protected function handlePaymentRefunded(array $resource): void
    {
        $customId = $resource['custom_id'] ?? null;
        
        if ($customId) {
            $order = Order::where('reference', $customId)->first();
            if ($order) {
                $order->update([
                    'status' => 'refunded',
                    'refunded_at' => now()
                ]);
            }
        }
        
        Log::info('PayPal payment refunded', [
            'order_reference' => $customId,
            'refund_id' => $resource['id']
        ]);
    }
    
    protected function isDuplicateEvent(string $eventId): bool
    {
        return Cache::has("webhook_processed:paypal:{$eventId}");
    }
    
    protected function markEventProcessed(string $eventId): void
    {
        // Store for 24 hours to prevent duplicate processing
        Cache::put("webhook_processed:paypal:{$eventId}", true, 86400);
    }
    
    protected function logWebhook(string $gateway, array $payload, string $status): void
    {
        WebhookLog::create([
            'gateway' => $gateway,
            'event_id' => $payload['id'] ?? null,
            'event_type' => $payload['event_type'] ?? null,
            'payload' => $payload,
            'status' => $status,
            'processed_at' => now()
        ]);
    }
}
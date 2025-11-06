<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\WebhookLog;
use App\Models\BankDeposit;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class BankDepositWebhookController extends Controller
{
    public function handle(Request $request): JsonResponse
    {
        try {
            // Validate admin authentication
            $this->validateAdminToken($request);
            
            // Check for duplicate processing
            $bankReference = $request->input('bank_reference');
            if ($this->isDuplicateEvent($bankReference)) {
                return response()->json(['message' => 'Webhook already processed'], 200);
            }
            
            // Log webhook receipt
            $this->logWebhook('bank_deposit', $request->all(), 'received');
            
            $this->handleBankDepositConfirmation($request);
            
            // Mark event as processed
            $this->markEventProcessed($bankReference);
            
            return response()->json(['status' => 'success'], 200);
            
        } catch (\Exception $e) {
            Log::warning('Webhook security violation', [
                'gateway' => 'bank_deposit',
                'ip' => $request->ip(),
                'reason' => $e->getMessage(),
                'payload_id' => $request->input('bank_reference')
            ]);
            
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }
    
    protected function validateAdminToken(Request $request): void
    {
        $authHeader = $request->header('Authorization');
        $adminToken = config('app.admin_webhook_token');
        
        if (!$authHeader || !$adminToken) {
            throw new \Exception('Missing authorization header or admin token');
        }
        
        // Extract token from "Bearer <token>" format
        if (!str_starts_with($authHeader, 'Bearer ')) {
            throw new \Exception('Invalid authorization header format');
        }
        
        $token = substr($authHeader, 7);
        
        if (!hash_equals($adminToken, $token)) {
            throw new \Exception('Invalid admin token');
        }
    }
    
    protected function handleBankDepositConfirmation(Request $request): void
    {
        $orderReference = $request->input('order_reference');
        $depositAmount = $request->input('deposit_amount');
        $depositDate = $request->input('deposit_date');
        $bankReference = $request->input('bank_reference');
        $verifiedBy = $request->input('verified_by');
        $notes = $request->input('notes', '');
        
        if (!$orderReference || !$depositAmount || !$bankReference) {
            throw new \Exception('Missing required bank deposit data');
        }
        
        $order = Order::where('reference', $orderReference)->first();
        if (!$order) {
            Log::warning('Bank deposit webhook order not found', ['reference' => $orderReference]);
            return;
        }
        
        if ($order->status !== 'pending') {
            Log::info('Bank deposit webhook order already processed', ['reference' => $orderReference]);
            return;
        }
        
        // Validate deposit amount matches order total
        if (abs($depositAmount - $order->total) > 0.01) {
            Log::warning('Bank deposit webhook amount mismatch', [
                'order_reference' => $orderReference,
                'expected' => $order->total,
                'received' => $depositAmount
            ]);
            
            // Create pending verification record
            $this->createBankDepositRecord($order, $request->all(), 'pending_verification');
            return;
        }
        
        // Create bank deposit record
        $bankDeposit = $this->createBankDepositRecord($order, $request->all(), 'confirmed');
        
        // Update order status
        $order->update([
            'status' => 'paid',
            'payment_gateway' => 'bank_deposit',
            'transaction_id' => $bankReference,
            'paid_at' => now(),
            'bank_deposit_id' => $bankDeposit->id
        ]);
        
        Log::info('Bank deposit confirmed', [
            'order_reference' => $orderReference,
            'bank_reference' => $bankReference,
            'amount' => $depositAmount,
            'verified_by' => $verifiedBy
        ]);
    }
    
    protected function createBankDepositRecord(Order $order, array $data, string $status): BankDeposit
    {
        return BankDeposit::create([
            'order_id' => $order->id,
            'bank_reference' => $data['bank_reference'],
            'deposit_amount' => $data['deposit_amount'],
            'deposit_date' => $data['deposit_date'],
            'verified_by' => $data['verified_by'],
            'verification_notes' => $data['notes'] ?? '',
            'status' => $status,
            'verified_at' => $status === 'confirmed' ? now() : null
        ]);
    }
    
    /**
     * Handle manual deposit verification from admin panel
     */
    public function manualVerification(Request $request): JsonResponse
    {
        $request->validate([
            'order_id' => 'required|exists:orders,id',
            'bank_reference' => 'required|string',
            'deposit_amount' => 'required|numeric|min:0',
            'deposit_date' => 'required|date',
            'verification_notes' => 'nullable|string|max:1000'
        ]);
        
        $order = Order::findOrFail($request->order_id);
        
        if ($order->status !== 'pending') {
            return response()->json(['error' => 'Order is not pending payment'], 400);
        }
        
        $bankDeposit = $this->createBankDepositRecord($order, [
            'bank_reference' => $request->bank_reference,
            'deposit_amount' => $request->deposit_amount,
            'deposit_date' => $request->deposit_date,
            'verified_by' => auth()->user()->email,
            'notes' => $request->verification_notes
        ], 'confirmed');
        
        $order->update([
            'status' => 'paid',
            'payment_gateway' => 'bank_deposit',
            'transaction_id' => $request->bank_reference,
            'paid_at' => now(),
            'bank_deposit_id' => $bankDeposit->id
        ]);
        
        Log::info('Manual bank deposit verification', [
            'order_id' => $order->id,
            'bank_reference' => $request->bank_reference,
            'verified_by' => auth()->user()->email
        ]);
        
        return response()->json([
            'message' => 'Bank deposit verified successfully',
            'order' => $order->load('bankDeposit')
        ]);
    }
    
    protected function isDuplicateEvent(string $bankReference): bool
    {
        return Cache::has("webhook_processed:bank_deposit:{$bankReference}");
    }
    
    protected function markEventProcessed(string $bankReference): void
    {
        // Store for 24 hours to prevent duplicate processing
        Cache::put("webhook_processed:bank_deposit:{$bankReference}", true, 86400);
    }
    
    protected function logWebhook(string $gateway, array $payload, string $status): void
    {
        WebhookLog::create([
            'gateway' => $gateway,
            'event_id' => $payload['bank_reference'] ?? null,
            'event_type' => 'deposit_confirmation',
            'payload' => $payload,
            'status' => $status,
            'processed_at' => now()
        ]);
    }
}
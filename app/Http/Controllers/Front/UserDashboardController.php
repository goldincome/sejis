<?php

namespace App\Http\Controllers\Front;

use App\Enums\OrderStatusEnum;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class UserDashboardController extends Controller
{
    protected $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    /**
     * Display the user dashboard with statistics and recent orders
     */
    public function index()
    {
        $user = Auth::user();
        
        // Get user statistics
        $stats = $this->orderService->getUserStats($user->id);
        
        // Get recent orders (last 5)
        $recentOrders = $this->orderService->getUserOrders($user->id, 5);
        
        // Get upcoming orders (next 3 upcoming kitchen rentals)
        $upcomingOrders = $this->orderService->getUpcomingOrders($user->id, 3);
        
        // Get loyalty information
        $loyaltyInfo = $this->orderService->getUserLoyaltyInfo($user->id);
        
        // Get profile completion
        $profileCompletion = $this->orderService->calculateProfileCompletion($user);
        
        return view('front.dashboard.index', compact(
            'stats', 
            'recentOrders', 
            'upcomingOrders', 
            'loyaltyInfo', 
            'profileCompletion'
        ));
    }

    /**
     * Display all user orders with filtering and pagination
     */
    public function orders(Request $request)
    {
        $user = Auth::user();
        $perPage = $request->input('per_page', 15);
        
        $orders = $this->orderService->getUserOrdersWithFilters($user->id, [
            'status' => $request->input('status'),
            'date_from' => $request->input('date_from'),
            'date_to' => $request->input('date_to'),
            'search' => $request->input('search')
        ], $perPage);
        
        // Get filter options
        $statuses = Order::distinct()->whereNotNull('status')->pluck('status');
        
        return view('front.dashboard.orders', compact('orders', 'statuses'));
    }

    /**
     * Display order details
     */
    public function orderDetails(Request $request, $orderReference)
    {
        $user = Auth::user();
        
        $order = Order::where('order_no', $orderReference)
            ->where('user_id', $user->id)
            ->with(['orderDetails.product', 'bankDeposit'])
            ->firstOrFail();
        
        // Get order timeline/status history
        $timeline = $this->orderService->getOrderTimeline($order->id);
        
        return view('front.dashboard.order-details', compact('order', 'timeline'));
    }

    /**
     * Download order invoice (PDF)
     */
    public function downloadInvoice($orderReference)
    {
        $user = Auth::user();
        
        $order = Order::where('order_no', $orderReference)
            ->where('user_id', $user->id)
            ->with(['orderDetails.product', 'user'])
            ->firstOrFail();
       
        if (!in_array($order->status->value, OrderStatusEnum::toArray() )) {
            return back()->with('error', 'Invoice is only available for paid orders.');
        }

        return $this->orderService->generateInvoicePDF($order);
    }

    /**
     * Cancel an order (if cancellation is allowed)
     */
    public function cancelOrder(Request $request, $orderReference)
    {
        $user = Auth::user();
        
        $order = Order::where('order_no', $orderReference)
            ->where('user_id', $user->id)
            ->firstOrFail();
        
        try {
            $result = $this->orderService->cancelOrder($order, $request->input('reason', 'Customer cancellation'));
            
            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'message' => 'Order cancelled successfully.'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => $result['message']
                ], 400);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Unable to cancel order. Please contact support.'
            ], 500);
        }
    }

    /**
     * Request refund for an order
     */
    public function requestRefund(Request $request, $orderReference)
    {
        $request->validate([
            'reason' => 'required|string|max:500'
        ]);
        
        $user = Auth::user();
        
        $order = Order::where('order_no', $orderReference)
            ->where('user_id', $user->id)
            ->firstOrFail();
        
        try {
            $result = $this->orderService->requestRefund($order, $request->input('reason'));
            
            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'message' => 'Refund request submitted successfully. We will review and respond within 2-3 business days.'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => $result['message']
                ], 400);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Unable to process refund request. Please contact support.'
            ], 500);
        }
    }

    /**
     * Get user notifications
     */
    public function notifications(Request $request): JsonResponse
    {
        $user = Auth::user();
        
        // Get unread notifications for the user
        $notifications = $user->notifications()
            ->when($request->input('unread_only'), function ($query) {
                return $query->whereNull('read_at');
            })
            ->latest()
            ->limit(20)
            ->get();
        
        return response()->json($notifications);
    }

    /**
     * Mark notification as read
     */
    public function markNotificationAsRead(Request $request, $notificationId): JsonResponse
    {
        $user = Auth::user();
        
        $notification = $user->notifications()->findOrFail($notificationId);
        $notification->markAsRead();
        
        return response()->json(['success' => true]);
    }

    /**
     * Get user profile completion percentage
     */
    public function getProfileCompletion(): JsonResponse
    {
        $user = Auth::user();
        
        $completionData = $this->orderService->calculateProfileCompletion($user);
        
        return response()->json($completionData);
    }

    /**
     * Get user loyalty points and rewards
     */
    public function loyaltyInfo(): JsonResponse
    {
        $user = Auth::user();
        
        $loyaltyData = $this->orderService->getUserLoyaltyInfo($user->id);
        
        return response()->json($loyaltyData);
    }
}

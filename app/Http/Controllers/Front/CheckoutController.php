<?php

namespace App\Http\Controllers\Front;

use App\Models\Order;
use Illuminate\Http\Request;
use App\Services\CartService;
use App\Enums\ProductTypeEnum;
use App\Services\OrderService;
use App\Enums\PaymentMethodEnum;
use App\Services\PaymentService;
use App\Services\SettingsService;
use App\Http\Controllers\Controller;

class CheckoutController extends Controller
{
     protected $paymentService;
    protected $cartService;
    protected $settingsService;
    public function __construct(PaymentService $paymentService, CartService $cartService,
       SettingsService $settingsService )
    {
        $this->paymentService = $paymentService;
        $this->cartService = $cartService;
        $this->settingsService = $settingsService;
    }

    public function index()
    {  
        if($this->cartService::content()->count() == 0){
            return to_route('cart.index');
        }
        
        try {
           $cartItems = $this->cartService::content();
           $productType =  ProductTypeEnum::class;
           $paymentMethods = PaymentMethodEnum::class;
            // Create a Stripe Setup Intent
            $intent = auth()->user()->createSetupIntent();
           $settingsService = $this->settingsService;

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error no product to checkout: ' . $e->getMessage())->withInput();
        }

        return view('front.checkout.index', compact('settingsService','cartItems', 'intent', 'productType', 'paymentMethods'));
    }

     /**
     * Process the payment based on the selected gateway.
     *
     * @param Request $request
     * 
     */
    public function store(Request $request)
    {  
        // Validate the request
        $request->validate([
            'payment_method' => 'required|in:paypal,stripe,bank,takepayment',
        ]);
        $description = '';
        $paymentMethod = $request->payment_method;

        $this->cartService::search(function ($cartItem, $rowId) use ($paymentMethod, $description) {
            $this->cartService::update($rowId, ['options'  => $this->cartService->getFromCartItem($paymentMethod, $cartItem)]);
            $description = $description. ' - ' .$cartItem->options->description; 
        });

               
        try {
            // Prepare payment data
            $paymentData = [
                'amount' => $this->cartService::total(),
                'tax' => $this->cartService::tax(),
                'description' => $description,
                'currency' => 'GBP',
                'quantity' => $this->cartService::count(),
                'user_id' => auth()->id(), // Assuming user is authenticated
                
                // Add more data as needed (e.g., card details, description)
            ];
            
            // Resolve the payment gateway dynamically based on payment_type
            $this->paymentService->setPaymentGateway($request->payment_method);

            // Process the payment
            $result = $this->paymentService->execute($paymentData);
            

        } catch (\Exception $e) {
            //dd($e->getMessage());
            return redirect()->route('cart.index')->with('error','Payment processing failed, Something went wrong with PayPal: '.$e->getMessage());
        }
    }

    public function paypalSuccess(Request $request, OrderService $orderService)
    {
        try {
            $paymentMethod = $this->cartService->getPaymentMethodFromCart();
            if ($request->has('token') && $paymentMethod === PaymentMethodEnum::PayPal->value) { 
                $paymentGateway = $this->paymentService->resolvePaymentGateway(PaymentMethodEnum::PayPal->value);
                $result = $paymentGateway->paymentSuccess($request);

                if ($result && $result['status'] === 'COMPLETED') {
                    $request['payment_method'] = PaymentMethodEnum::PayPal->value;
                    $order = $orderService->createOrder($request->all());
                    $this->cartService::destroy();
                    return redirect()->route('checkout.success', $order)->with('success', 'Payment successful!');
                }
            }
            return redirect()->route('cart.index')->with('error', 'Payment not successful');
        } catch (\Exception $e) {
            //dd($e->getMessage());
            return redirect()->route('cart.index')->with('error', 'Payment confirmation failed: ' . $e->getMessage());
        }
    }

    public function stripeSuccess(Request $request, OrderService $orderService)
    {   
        try {

            $paymentMethod = $this->cartService->getPaymentMethodFromCart();

            $paymentGateway = $this->paymentService->resolvePaymentGateway($paymentMethod);
            $result = $paymentGateway->paymentSuccess($request);

            $request['payment_method'] = PaymentMethodEnum::Stripe->value;
            $order = $orderService->createOrder($request->all());
            $this->cartService::destroy();
            //dd($result);
            return redirect()->route('user.checkout.success', $order)->with('success', 'Payment successful!');
        } catch (\Exception $e) {   
            return redirect()->route('cart.index')->with('error', 'Payment confirmation failed: ' . $e->getMessage());
        }
    }
    
    public function takepaymentSuccess(Request $request, OrderService $orderService)
    {   
        try {

            $paymentMethod = $this->cartService->getPaymentMethodFromCart();
            //dd($request->all(), $paymentMethod);
            $paymentGateway = $this->paymentService->resolvePaymentGateway($paymentMethod);
            $result = $paymentGateway->paymentSuccess($request);

            $request['payment_method'] = $paymentMethod;
;
            $order = $orderService->createOrder($request->all());
            $this->cartService::destroy();
            return redirect()->route('user.checkout.success', $order)->with('success', 'Payment successful!');
        } catch (\Exception $e) {
        
            //dd($e->getMessage());
            return redirect()->route('cart.index')->with('error', 'Payment confirmation failed: ' . $e->getMessage());
        }
    }

    public function checkoutSuccess(Order $order)
    {   
        $order->load('orderDetails');
        return view('front.checkout.success',compact('order'));
    }

    public function paymentCancelled()
    {
        //dd($request->all());
        return redirect()->route('user.checkout.index')->with('error','Payment was cancelled.');
    }
}

<?php
namespace App\Services\PaymentGateways;


use Exception;
use Stripe\Stripe;
use App\Models\Order;
use Illuminate\Http\Request;
use Stripe\Checkout\Session;
use App\Services\CartService;
use App\Services\PaymentGateways\PaymentGatewayInterface;

class StripeGateway implements PaymentGatewayInterface
{
    protected $cartService;

    public function __construct(CartService $cartService)
    {
        $this->cartService = $cartService;
         Stripe::setApiKey(config('cashier.secret'));
        
    }

    public function charge($paymentData)
    { 
        try 
        {   

            $user = auth()->user();
           // $stripeCustomer = $user->createOrGetStripeCustomer();
           
             $session = Session::create([
                    'payment_method_types' => ['card'],
                    'line_items'           => $this->addLineItems(), // Pass the dynamically created array
                    'customer_email'       => $user->email,
                    'metadata'             => [
                                                'customer_number' => $user->customer_no, // Assuming you have this on your user model
                                                'customer_name' => $user->name,
                                            ],
                    'mode'                 => 'payment',
                    'success_url'          => route('user.stripe.success') . '?session_id={CHECKOUT_SESSION_ID}',
                    'cancel_url'           => route('user.payment.cancelled'),
                ]);
            //only products that are not subscription products
            //$session = $user->checkout($returnUrls);
            
            return redirect()->away($session->url)->send(); //redirect($session->url);

        }  catch (\Stripe\Exception\CardException $e) {
            // Since it's a decline, \Stripe\Exception\CardException will be caught
            throw new Exception('Card was declined: ' . $e->getDeclineCode());
        } catch (\Stripe\Exception\ApiErrorException $e) {
            // Display a generic error to the user for other Stripe API errors.
            throw new Exception('Something went wrong with the payment gateway: ' . $e->getMessage());
        } catch (Exception $e) {
            // Something else happened, completely unrelated to Stripe
            throw new Exception('An unexpected error occurred: ' . $e->getMessage());
        }
    }

    protected function addLineItems()
    {
        $lineItems = [];
        foreach ($this->cartService::content() as $item) {
            $lineItems[] = [
                'price_data' => [
                    'currency'     => strtolower(config('cashier.currency')),
                    'product_data' => [
                        'name' => $item->name,
                         'description' => $item->name. " ". $item->description,
                        'images' => $item->image,
                    ],
                    // Price must be in the smallest currency unit (e.g., cents)
                    // We multiply the price by 100 and cast to integer
                    'unit_amount'  => (int)($item->price * 100),
                ],
                'quantity'   => $item->qty,
            ];
        }
        if (config('cart.tax') > 0) {
            $lineItems[] = [
                'price_data' => [
                    'currency'     => strtolower(config('cashier.currency')),
                    'product_data' => [
                        'name' => 'VAT ('.config('cart.tax')."%)",
                    ],
                    'unit_amount'  => $this->cartService::tax() * 100,
                ],
                'quantity'   => 1,
            ];
        }
        return $lineItems;
    }

    public function paymentSuccess(Request $request)
    {
        //Stripe::setApiKey(config('services.stripe.secret'));
        $sessionId = $request->get('session_id');
        $user = auth()->user();
        try{ 
             $session = Session::retrieve($sessionId);
            //dd($session->metadata);
            //  Verify the session and payment status
            if ($session->status !== 'complete' || $session->payment_status !== 'paid') {
                // The payment was not successful, redirect to cancel page
                throw new Exception('An unexpected error occurred: Payment was not successful.');
            }
            $metadata = $session->metadata;
            $customerNo = $metadata->customer_number;

            $existingOrder = Order::where('stripe_payment_intent', $session->payment_intent)->first();
            if ($existingOrder) {
                // Order already processed. Just show the success page.
                return true;
            }

            if($user->customer_no == $customerNo){
                return true;
                dd('weeee');
            }
            return false;

        }catch (Exception $e) {
            // Something else happened, completely unrelated to Stripe
            throw new Exception('An unexpected error occurred: ' . $e->getMessage());
        }

    }
}
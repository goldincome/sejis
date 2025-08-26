<?php
namespace App\Services\PaymentGateways;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Srmklive\PayPal\Services\PayPal as PayPalClient;
use App\Services\PaymentGateways\PaymentGatewayInterface;

class PaypalGateway implements PaymentGatewayInterface
{

     protected $provider;

    public function __construct()
    {
        $this->provider = new PayPalClient;
        $this->provider->setApiCredentials(config('paypal'));
        $this->provider->getAccessToken();
    }

    public function charge(array $paymentData)
    {
        try{
            $cartTotal = (float) str_replace(',', '', $paymentData['amount']);
            $payData = [
                "intent" => "CAPTURE",
                "application_context" => [
                    "return_url" => route('user.paypal.success'),
                    "cancel_url" => route('user.payment.cancelled'),
                ],
                "purchase_units" => [[
                    "amount" => [
                        "currency_code" => $paymentData['currency'],
                        "value" => number_format($cartTotal, 2, '.', ''),
                    ],
                    "custom_id" => auth()->id(),
                    "description" => "Order for ". $paymentData['description']
                ]],
            ];
            
            $order = $this->provider->createOrder($payData);

            if (isset($order['id']) && $order['status'] === 'CREATED') {
                foreach ($order['links'] as $link) {
                    if ($link['rel'] === 'approve') {
                        return Redirect::away($link['href'])->send();
                    }
                }
            }
        } catch (\Exception $e) {
            throw new \Exception('Something went wrong with PayPal.'.$e->getMessage());
        }
    }

     public function paymentSuccess(Request $request)
    {
        $orderId = $request->get('token');

        $result = $this->provider->capturePaymentOrder($orderId);

        if ($result['status'] === 'COMPLETED') {
            // Store order in your database here
            // Example:
            // Order::create([
            //     'user_id' => auth()->id(),
            //     'amount' => $result['purchase_units'][0]['amount']['value'],
            //     'paypal_order_id' => $orderId,
            //     'status' => 'completed',
            // ]);
            return $result;
            
        }

        return false;
    }

}
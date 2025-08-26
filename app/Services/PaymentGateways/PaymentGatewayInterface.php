<?php
namespace App\Services\PaymentGateways;

use Illuminate\Http\Request;

interface PaymentGatewayInterface
{

    public function charge(array $paymentData);
    public function paymentSuccess(Request $request);

}
<?php
namespace App\Services\PaymentGateways;

use App\Services\PaymentGateways\PaymentGatewayInterface;


class BankDepositGateway implements PaymentGatewayInterface
{

     public function charge($paymentData)
    {
        //more logic here

        return[
            'amount' => $paymentData['amount'],
            'currency' => 'gbp',
            'source' => $paymentData['token'],
            'description' => $paymentData['description'],
        ];
    }

}
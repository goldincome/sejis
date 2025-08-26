<?php
namespace App\Services\PaymentGateways\TakePayments;


enum Action: string
{
    case PreAuth = 'PREAUTH';
    case Verify = 'VERIFY';
    case Sale = 'SALE';
    case Refund = 'REFUND';
    case RefundSale = 'REFUND_SALE';
}
<?php
namespace App\Services\PaymentGateways\TakePayments;


enum Type: int
{
    case Ecommerce = 1;
    case MailOrder = 2;
    case ContinuousAuthority = 9;
}
<?php
namespace App\Enums;

enum PaymentMethodEnum: string
{
    case Stripe = 'stripe';
    case PayPal = 'paypal';
    case Takepayment = 'takepayment';
    case Bank = 'bank';

    public function label(): string
    {
        return match ($this) {
            self::Stripe => 'Stripe',
            self::PayPal=> 'Paypal',
            self::Bank => 'Bank Deposit',
            self::Takepayment => 'Takepayment'
        };
      
    }

    public static function toArray(): array
    {
        return array_column(self::cases(), 'value');
    }
}
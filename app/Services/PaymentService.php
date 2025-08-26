<?php
namespace App\Services;

use App\Enums\PaymentMethodEnum;

use App\Services\PaymentGateways\PaypalGateway;
use App\Services\PaymentGateways\StripeGateway;
use App\Services\PaymentGateways\BankDepositGateway;
use App\Services\PaymentGateways\PaymentGatewayInterface;
use App\Services\PaymentGateways\TakePaymentGateway;

class PaymentService
{
    protected $paymentGateway;

    public function __construct(?PaymentGatewayInterface $paymentGateway = null )
    {
        $this->paymentGateway = $paymentGateway;
    }
    /**
     * Set the payment gateway dynamically.
     *
     * @param PaymentGatewayInterface $paymentGateway
     * @return void
     */
    public function setPaymentGateway(string $paymentType)
    {
        $this->paymentGateway = $this->resolvePaymentGateway($paymentType); //$paymentGateway;
    }
    
    /**
     * Resolve the payment gateway based on the payment type.
     *
     * @param string $paymentType
     * @return PaymentGatewayInterface
     * @throws \InvalidArgumentException
     */
    public function resolvePaymentGateway(string $paymentType): PaymentGatewayInterface
    {
        switch ($paymentType) {
            case PaymentMethodEnum::PayPal->value:
                return app(PaypalGateway::class);
            case PaymentMethodEnum::Stripe->value:
                return app(StripeGateway::class);
            case PaymentMethodEnum::Takepayment->value:
                return app(TakePaymentGateway::class);
            case PaymentMethodEnum::Bank->value:
                return app(BankDepositGateway::class);
            default:
                throw new \InvalidArgumentException("Unsupported payment type: {$paymentType}");
        }
    }
    /**
     * Execute the payment using the selected gateway.
     *
     * @param array $paymentData
     * @return mixed
     * @throws \Exception
     */
    public function execute(array $paymentData)
    {
        if (!$this->paymentGateway) {
            throw new \Exception('No payment gateway set.');
        }
        return $this->paymentGateway->charge($paymentData);
    }


}
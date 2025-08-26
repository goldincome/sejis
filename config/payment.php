<?php

return [
    'default' => env('PAYMENT_GATEWAY', 'paypal'),
    
    'gateways' => [
        'paypal' => [
            'mode' => env('PAYPAL_MODE', 'sandbox'),
            'client_id' => env('PAYPAL_CLIENT_ID'),
            'client_secret' => env('PAYPAL_CLIENT_SECRET'),
            'return_url' => env('PAYPAL_RETURN_URL'),
            'cancel_url' => env('PAYPAL_CANCEL_URL'),
        ],
        
        'takepayments' => [
            'merchant_id' => env('TAKEPAYMENTS_MERCHANT_ID'),
            'password' => env('TAKEPAYMENTS_PASSWORD'),
            'prefix' => env('TAKEPAYMENTS_PREFIX'),
            'return_url' => env('TAKEPAYMENTS_RETURN_URL'),
            'cancel_url' => env('TAKEPAYMENTS_CANCEL_URL'),
            'currency' => env('TAKEPAYMENTS_CURRENCY', 'GBP'),
        ]
    ],
    
    'currency' => env('PAYMENT_CURRENCY', 'USD'),
];
<?php

return [
    /*
     * ---------------------------------------------------------------
     * Takepayments
     * ---------------------------------------------------------------
     *
     * Api Key
     */
    'access_key' => env('TAKEPAYMENTS_ACCESS_KEY', 'jENU6FiBq4bNAs6E3sJ7'),

    'test_access_key' => env('TAKEPAYMENTS_TEST_ACCESS_KEY', '9GXwHNVC87VqsqNM'),

    /*
     * ---------------------------------------------------------------
     * Takepayments
     * ---------------------------------------------------------------
     *
     * Merchant ID
     */
    'merchant_id' => env('TAKEPAYMENTS_MERCHANT_ID', '256287'),

    'test_merchant_id' => env('TAKEPAYMENTS_TEST_MERCHANT_ID', '119837'),

    /*
     * ---------------------------------------------------------------
     * Takepayments
     * ---------------------------------------------------------------
     *
     * Takepayments hosted endpoint
     */
    'hosted_url' => env('TAKEPAYMENTS_HOSTED_URL', 'https://gw1.tponlinepayments.com/hosted/'),

     /*
     * ---------------------------------------------------------------
     * Takepayments
     * ---------------------------------------------------------------
     *
     * Api direct endpoint 
     */
    'direct_url' => env('TAKEPAYMENTS_DIRECT_URL', 'https://gw1.tponlinepayments.com/direct/'),

     /*
     * ---------------------------------------------------------------
     * Takepayments
     * ---------------------------------------------------------------
     *
     * country and currency code
     */
    'country_code' => env('TAKEPAYMENTS_COUNTRY_CODE', 826),
    'currency_code' => env('TAKEPAYMENTS_CURRENCY_CODE', 826),

    'is_live' => false,
];
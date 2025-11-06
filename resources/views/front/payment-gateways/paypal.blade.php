 @if($settingsService->get('paypal_enabled'))
    <div class="payment-method payment-option p-4 rounded-lg cursor-pointer flex items-center justify-between"
        data-method="{{ $payMethod->value }}" onclick="selectPayment('paypal')">
        <input type="radio" name="payment_method" value="{{ $payMethod->value }}" class="sr-only"   />
        <div>
            <h3 class="font-semibold text-blue-800">PayPal</h3>
            <p class="text-sm text-gray-600">Pay securely using your PayPal account.</p>
        </div>
        <img src="https://www.paypalobjects.com/webstatic/mktg/logo/bdg_payments_by_pp_2line.png"
            alt="PayPal Logo" class="h-8">
    </div>
 @endif

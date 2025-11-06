@if($settingsService->get('stripe_enabled'))
    <div class="payment-method payment-option p-4 rounded-lg cursor-pointer flex items-center justify-between"
        data-method="{{ $payMethod->value }}" onclick="selectPayment('card')"  >
        <input type="radio" name="payment_method" value="{{ $payMethod->value }}" class="sr-only"
            onclick="selectPayment('card')" />
        <div>
            <h3 class="font-semibold text-blue-800">Credit or Debit Card</h3>
            <p class="text-sm text-gray-600">Secure payment via Stripe.</p>
        </div>
        <div>
            <i class="fab fa-cc-visa text-2xl text-gray-500 mx-1"></i>
            <i class="fab fa-cc-mastercard text-2xl text-gray-500 mx-1"></i>
            <i class="fab fa-cc-amex text-2xl text-gray-500 mx-1"></i>
        </div>
    </div>
@endif

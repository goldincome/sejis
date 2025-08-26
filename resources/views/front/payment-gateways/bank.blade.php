<div class="payment-method payment-option p-4 rounded-lg cursor-pointer flex items-center justify-between"
     data-method="{{ $payMethod->value}}" onclick="selectPayment('bank')">
     <input type="radio" name="payment_method" value="{{ $payMethod->value}}" class="sr-only" onclick="selectPayment('bank')" />
<div>
        <h3 class="font-semibold text-blue-800">Bank Deposit</h3>
        <p class="text-sm text-gray-600">Pay direct to the Bank.</p>
    </div>

 </div>

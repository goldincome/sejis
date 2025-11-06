@extends('layouts.app')

@php
    use App\Enums\ProductTypeEnum; // Import the Enum to use in the view
@endphp

@section('title', 'Checkout - Sejis Rentals')

@section('description', 'Checkout - Sejis Rentals')


@section('content')
    <main class="container mx-auto px-6 py-16 md:py-10">
        @include('front.common.error-and-message')
        <h1 class="text-3xl md:text-4xl font-bold text-center text-blue-800 mb-12">Complete Your Order</h1>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-12">

            <div class="md:col-span-2 bg-white p-8 rounded-lg shadow-md border border-gray-200">
                <h2 class="text-2xl font-semibold text-blue-700 mb-6 border-b pb-3">1. Order Summary</h2>

                @foreach ($cartItems as $item)
                    <div class="flex items-center justify-between border-b py-4">
                        {{-- Product Image --}}
                        <div class="w-16 h-16 overflow-hidden rounded ">
                            <img src="{{ $item->options->image }}" alt="{{ $item->name }}"
                                class="w-full h-full object-cover">
                        </div>

                        {{-- Product Name and Details --}}
                        <div class="flex-1 px-4">
                            <h4 class="font-semibold text-blue-800 ">{{ $item->name }}</h4>
                            
                            {{-- Check Product Type --}}
                            @if($item->options->product_type == ProductTypeEnum::KITCHEN_RENTAL->value)
                                <!-- KITCHEN RENTAL DETAILS -->
                                <div class="text-sm text-gray-600">
                                    Date: {{ \Carbon\Carbon::parse($item->options->booking_date)->format('D, M j, Y') }}
                                    @foreach ($item->options->booking_time_display as $timeDisplay)
                                        <p class="text-sm text-gray-500"><span class="text-blue-800">Time:</span>
                                            {{ $timeDisplay }} (1 hour)
                                        </p>
                                    @endforeach
                                </div>
                            @elseif($item->options->product_type == ProductTypeEnum::ITEM_RENTAL->value)
                                <!-- EQUIPMENT RENTAL DETAILS -->
                                <div class="text-sm text-gray-600">
                                    Start: {{ \Carbon\Carbon::parse($item->options->start_date)->format('D, M j, Y') }}
                                    <br>
                                    End: {{ \Carbon\Carbon::parse($item->options->end_date)->format('D, M j, Y') }}
                                    <br>
                                    <span class="text-blue-800">Duration:</span> {{ $item->options->rental_duration }} day(s)
                                </div>
                            @endif
                        </div>

                        {{-- Unit Price / Quantity --}}
                        <div class="w-24 text-right">
                            @if($item->options->product_type == ProductTypeEnum::KITCHEN_RENTAL->value)
                                <div class="text-blue-800 font-medium">{{ $item->qty }} hour(s) X
                                    {{ currencyFormatter($item->price) }}</div>
                            @elseif($item->options->product_type == ProductTypeEnum::ITEM_RENTAL->value)
                                <div class="text-blue-800 font-medium">{{ $item->qty }} unit(s) X
                                    {{ currencyFormatter($item->price) }}</div>
                            @else
                                <div class="text-blue-800 font-medium">{{ $item->qty }} X
                                    {{ currencyFormatter($item->price) }}</div>
                            @endif
                        </div>

                        {{-- Subtotal --}}
                        <div class="w-28 text-right font-bold text-blue-800 ">
                            {{ currencyFormatter($item->subtotal) }}
                        </div>
                    </div>
                @endforeach
                
                <div class="border-t pt-4 mt-4">
                    <div class="flex justify-between items-center text-blue-800 text-lg">
                        <span>Subtotal</span> <span>{{ currencyFormatter(Cart::subtotal(2, '.', '')) }}</span>
                    </div>
                </div>
                <div class="border-t pt-4 mt-4">
                    <div class="flex justify-between items-center text-blue-800  text-lg">
                        <span>Tax</span> <span>{{ currencyFormatter(Cart::tax(2, '.', '')) }}</span>
                    </div>
                </div>
                <div class="border-t pt-4 mt-4">
                    <div class="flex justify-between items-center text-blue-800 font-bold text-lg">
                        <span>Total Due Today</span> <span>{{ currencyFormatter(Cart::total(2, '.', '')) }}</span>
                    </div>
                </div>

                <div
                    class="mt-6 p-3 bg-orange-100 border border-orange-300 text-orange-700 rounded-md text-sm flex items-center">
                    <i class="fas fa-clock mr-2 animate-pulse"></i>
                    <span>Limited time offer! Complete your purchase within <strong>15 minutes</strong> to secure this
                        pricing.</span>
                </div>

                <h2 class="text-2xl font-semibold text-blue-700 mt-10 mb-6 border-b pb-3">2. Select Payment Method</h2>
                <form action="{{ route('user.process.payment') }}" method="POST" id="payment-form">
                    @csrf
                    <div class="space-y-4">
                        @foreach ($paymentMethods::cases() as $payMethod)
                            @include('front.payment-gateways.' . $payMethod->value)
                        @endforeach
                    </div>

                    {{-- Stripe Card Element --}}
                    <div id="stripe-card-element" class="mt-4 hidden">
                        <div id="card-element" class="p-4 border rounded-lg bg-gray-50">
                        </div>
                        <div id="card-errors" role="alert" class="text-red-600 text-sm mt-2"></div>
                    </div>


                    <div class="mt-8 text-center text-sm text-gray-500 flex items-center justify-center space-x-4"> <span
                            class="secure-badge bg-green-100 text-green-700">
                            <i class="fas fa-lock mr-1"></i> SSL Secured Connection
                        </span>
                        <span class="secure-badge bg-blue-100 text-blue-700">
                            <i class="fas fa-shield-alt mr-1"></i> Verified Payment Gateway
                        </span>
                    </div>

                    <button type="submit" id="submit-button"
                        class="mt-10 w-full bg-orange-500 hover:bg-orange-600 text-white font-bold py-4 px-8 rounded-lg text-lg transition duration-300 shadow-lg transform hover:scale-105">
                        Complete Purchase <i class="fas fa-arrow-right ml-2"></i>
                    </button>
                    <p class="text-xs text-gray-500 text-center mt-4">By clicking "Complete Purchase", you agree to our <a
                            href="#" class="underline hover:text-blue-700">Terms of Service</a> and <a href="#"
                            class="underline hover:text-blue-700">Privacy Policy</a>.</p>
                </form>
            </div>

            <div class="md:col-span-1 space-y-8">
                <div class="bg-white p-6 rounded-lg shadow-md border border-gray-200 text-center"> <img
                        src="https://placehold.co/80x80/34d399/ffffff?text=?" alt="Guarantee Badge"
                        class="mx-auto mb-4 rounded-full">
                    <h3 class="text-lg font-semibold text-blue-700 mb-2">Our Satisfaction Guarantee</h3>
                    <p class="text-gray-600 text-sm leading-relaxed"> We stand by our services. If you're not completely
                        satisfied within the first 30 days, contact us for a hassle-free refund. Your business success
                        is our priority.
                    </p>
                </div>

                <div class="bg-white p-6 rounded-lg shadow-md border border-gray-200">
                    <h3 class="text-lg font-semibold text-blue-700 mb-4 text-center">Why Choose VMC?</h3>
                    <ul class="space-y-3 text-sm text-gray-700">
                        <li class="flex items-start"><i
                                class="fas fa-check-circle text-green-500 mr-2 mt-1"></i><span>Prestigious London
                                Business Address.</span></li>
                        <li class="flex items-start"><i
                                class="fas fa-check-circle text-green-500 mr-2 mt-1"></i><span>Secure Mail Handling &
                                Forwarding.</span></li>
                        <li class="flex items-start"><i
                                class="fas fa-check-circle text-green-500 mr-2 mt-1"></i><span>Flexible Plans to Suit
                                Your Needs.</span></li>
                        <li class="flex items-start"><i
                                class="fas fa-check-circle text-green-500 mr-2 mt-1"></i><span>Dedicated Customer
                                Support.</span></li>
                    </ul>
                </div>

                <div class="bg-blue-50 p-6 rounded-lg border border-blue-200 text-center"> <i
                        class="fas fa-headset text-3xl text-blue-600 mb-3"></i>
                    <h3 class="text-lg font-semibold text-blue-700 mb-2">Need Assistance?</h3>
                    <p class="text-gray-600 text-sm mb-4"> Have questions about the checkout process or our services?
                        Our team is here to help!
                    </p>
                    <a href="contact-us.html"
                        class="text-sm font-medium text-orange-600 hover:text-orange-700 hover:underline"> Contact
                        Support <i class="fas fa-arrow-right ml-1"></i> </a>
                </div>

            </div>

        </div>
    </main>
@endsection

@section('css')
    <style>
        .payment-option {
            border: 1px solid #e5e7eb;
            /* gray-200 */
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
        }

        .payment-option:hover,
        .payment-option.selected {
            border-color: #2563eb;
            /* blue-600 */
            box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.3);
        }

        footer a:not(.social-icon) {
            /* */
            @apply hover:text-orange-300 transition duration-300 hover:underline;
            /* */
        }

        .secure-badge {
            display: inline-flex;
            align-items: center;
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
            font-size: 0.8rem;
            font-weight: 500;
        }
    </style>
@endsection

@section('js')
    <script>
        function selectPayment(method) {
            // Remove selected state from all options
            document.querySelectorAll('.payment-option').forEach(el => el.classList.remove('selected'));
            // Add selected state to the clicked option
            event.currentTarget.classList.add('selected');

            const cardDetails = document.getElementById('card-details');
            if (method === 'card') {
                cardDetails.classList.remove('hidden');
            } else {
                cardDetails.classList.add('hidden');
            }

            // In a real implementation, you would store the selected method
            console.log("Selected payment method:", method);
        }

        document.querySelectorAll('.payment-method').forEach(method => {
            method.addEventListener('click', () => {
                document.querySelectorAll('.payment-method').forEach(el => {
                    el.classList.remove('border-blue-500', 'ring', 'ring-blue-300');
                    const input = el.querySelector('input[type="radio"]');
                    if (input) input.checked = false;
                });

                method.classList.add('border-blue-500', 'ring', 'ring-blue-300');
                const selectedInput = method.querySelector('input[type="radio"]');
                if (selectedInput) selectedInput.checked = true;
            });
        });

    </script>
@endsection
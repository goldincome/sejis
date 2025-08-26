@extends('layouts.app')

@section('title', 'Your Cart - Sejis Rentals')
@section('css')
    <style>
        html { scroll-behavior: smooth; }
        body { font-family: 'roboto', sans-serif; color: theme('colors.brand-text-dark'); }
        .page-hero-section {
            background: linear-gradient(rgba(44, 62, 80, 0.7), rgba(44, 62, 80, 0.7)), url('https://images.pexels.com/photos/7088481/pexels-photo-7088481.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=1') no-repeat center center;
            background-size: cover;
            background-attachment: fixed;
            padding: 6rem 0;
        }
        
        /* Animation styles */
        .section-animate { 
            opacity: 0;
            transition: opacity 0.8s ease-out, transform 0.8s ease-out;
        }
        .section-animate.visible { 
            opacity: 1;
            animation: fadeInUp 0.8s ease-out forwards; 
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
@endsection
@section('content')
    <main>
        <section class="page-hero-section text-white">
            <div class="container mx-auto px-4 sm:px-6 lg:px-8 text-center animate-fade-in-up">
                <h1 class="text-4xl sm:text-5xl font-bold mb-4 font-pacifico">Your Shopping Cart</h1>
                <p class="text-lg sm:text-xl max-w-2xl mx-auto text-brand-light-blue">Review your selections before
                    proceeding to checkout.</p>
            </div>
        </section>

        <section class="py-16 lg:py-24 bg-white">
            <div class="container mx-auto px-4 sm:px-6 lg:px-8">
                @if(Cart::count() == 0) {{-- Check if cart is empty --}}
                    <div class="text-center bg-white p-10 rounded-lg shadow-md">
                        <i class="fas fa-shopping-cart text-6xl text-gray-300 mb-4"></i>
                        <p class="text-xl text-gray-600 mb-6">Your cart is currently empty.</p>
                        <a href="{{ url('/') }}" class="bg-orange-500 hover:bg-orange-600 text-white font-bold py-3 px-6 rounded-lg transition duration-300 shadow-md text-lg">
                            Continue Shopping
                        </a>
                    </div>
                @else
                    <div class="bg-white p-6 md:p-8 rounded-2xl shadow-2xl section-animate">
                        <h2 class="text-3xl font-bold text-brand-deep-ash mb-8">Your Items</h2>
                        @include('front.common.error-and-message')
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Product</th>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Details</th>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Price</th>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Hour(s)</th>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Subtotal</th>
                                        <th scope="col" class="relative px-6 py-3"><span class="sr-only">Remove</span></th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($cartItems as $item)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    <div class="flex-shrink-0 h-16 w-16">
                                                        <img class="h-16 w-16 rounded-md object-cover"
                                                            src="{{ $item->options->image ?: 'https://images.pexels.com/photos/3771120/pexels-photo-3771120.jpeg?auto=compress&cs=tinysrgb&w=100&h=100&fit=crop' }}"
                                                            alt="ProChef Station">
                                                    </div>
                                                    <div class="ml-4">
                                                        <div class="text-sm font-medium text-gray-900">{{ $item->name }}</div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                Date: {{ \Carbon\Carbon::parse($item->options->booking_date)->format('D, M j, Y') }}
                                                <br> @foreach($item->options->booking_time_display as  $timeDisplay)
                                                                @php $duration = 1; @endphp
                                                                <p class="text-sm text-gray-500 mt-1"><span class="text-blue-800">Time:</span> {{ $timeDisplay }} ({{ $duration }} hour{{ $item->qty > 1 ? 's' : '' }})</p>
                                                                @php $duration = 0; @endphp
                                                            @endforeach
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ currencyFormatter($item->price) }} / hour</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $item->qty }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900">{{ currencyFormatter($item->subtotal(2, '.', '')) }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                <form action="{{ route('cart.remove') }}" method="POST">
                                                    @csrf
                                                    <input type="hidden" name="rowId" value="{{ $item->rowId }}">
                                                    <button type="submit" class="text-red-600 hover:text-red-900" aria-label="Remove item" title="Remove item">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-8 flex flex-col items-end">
                            <div class="w-full max-w-sm">
                                <div class="bg-gray-50 p-6 rounded-lg">
                                    <div class="flex justify-between text-gray-700">
                                        <span>Subtotal</span>
                                        <span>{{ currencyFormatter(Cart::subtotal(2, '.', '')) }}</span>
                                    </div>
                                    <div class="flex justify-between text-gray-700 mt-2">
                                        <span>Taxes ({{ config('cart.tax') }}%)</span>
                                        <span>{{ currencyFormatter(Cart::tax(2, '.', '')) }}</span>
                                    </div>
                                    <div class="flex justify-between font-bold text-xl text-brand-deep-ash mt-4 pt-4 border-t">
                                        <span>Total</span>
                                        <span>{{ currencyFormatter(Cart::total(2, '.', '')) }}</span>
                                    </div>
                                    <a href="{{ route('user.checkout.index') }}"
                                        class="mt-6 w-full flex justify-center py-3 px-4 border border-transparent rounded-md shadow-lg text-lg font-bold text-brand-deep-ash bg-accent hover:bg-accent-darker focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-accent-darker transition duration-300">
                                        Proceed to Checkout
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </section>
    </main>
@endsection

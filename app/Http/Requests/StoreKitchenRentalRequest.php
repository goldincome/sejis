<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreKitchenRentalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // or use Gate/Policy here
    }

    public function rules(): array
    {
        return [
            'product_id' => ['required', 'exists:products,id'],
            'booking_date' => ['required', 'date'],
            'booking_time' => ['required', 'array'],
        ];
    }
}


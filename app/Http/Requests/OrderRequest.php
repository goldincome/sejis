<?php

namespace App\Http\Requests;

use App\Enums\OrderStatusEnum;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Foundation\Http\FormRequest;

class OrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
         return [
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'total' => ['required', 'numeric', 'min:0'],
            'sub_total' => ['required', 'numeric', 'min:0'],
            'tax' => ['required', 'numeric', 'min:0'],
            'payment_method' => ['required', 'string', 'max:255'],
            //'payment_method_order_id' => ['nullable', 'string', 'max:255'],
            'currency' => ['required', 'string', 'max:255'],
            'status' => ['required', new Enum(OrderStatusEnum::class)],
        ];
    }
}

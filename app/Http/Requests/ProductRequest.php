<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductRequest extends FormRequest
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
       $rules = [
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'intro' => 'required|string',
            'category_id' => 'required|exists:categories,id',
            'price' => 'required|numeric|min:0.01',
            'price_per_day' => 'nullable|numeric',
            'main_product_image' => 'required|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
            'additional_images' => 'nullable|array|max:5',
            'additional_images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
            'is_active' => 'required|boolean',
        ];

        // For update, make images optional
        if ($this->isMethod('PUT') || $this->isMethod('PATCH')) {
            $rules['main_product_image'] = 'nullable|image|max:2048';
            $rules['additional_images.*'] = 'nullable|image|max:2048';
            $rules['existing_main_image.*'] = 'nullable|string';
             $rules['existing_additional_images.*'] = 'nullable|string';
        }

        return $rules;
    }

    public function messages()
    {
        return [
            'category_id.required' => 'Please select a category',
            'price.min' => 'Price must be at least 0.01',
            'main_product_image.max' => 'The primary image must not be larger than 2MB',
            'additional_images.*.max' => 'Gallery images must not be larger than 2MB each'
        ];
    }
}

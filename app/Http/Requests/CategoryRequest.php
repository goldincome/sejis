<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CategoryRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $categoryId = $this->route('category') ? $this->route('category')->id : null;

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('categories')->ignore($categoryId)
            ],
            'description' => 'nullable|string',
            'product_type' => 'required|string',
            'image' => 'nullable|image|max:2048'
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'Category name is required',
            'name.unique' => 'This category name already exists',
            'product_type.required' => 'Selected Product Type is required',
            'image.image' => 'Uploaded file must be an image',
            'image.max' => 'Image size cannot exceed 2MB'
        ];
    }
}
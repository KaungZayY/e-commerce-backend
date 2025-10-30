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
        return [
            'product_name' => 'required|string|max:255',
            'images' => 'nullable|array',
            'price' => 'required|numeric|min:0',
            'qty' => 'required|integer|min:0',

            'description' => 'nullable|string',
            'is_popular' => 'nullable|boolean',
            'moq' => 'nullable|integer|min:1',
            'discount_type' => 'nullable|string',
            'discount_amount' => 'nullable|numeric|min:0',
            'created_by' => 'nullable|exists:users,id',
            'category_id' => 'required|exists:categories,id',
        ];
    }

    public function attributes()
    {
        return [
            'category_id' => 'category',
        ];
    }
}

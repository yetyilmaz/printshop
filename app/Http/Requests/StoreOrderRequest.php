<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrderRequest extends FormRequest
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
            'order_type' => 'required|in:stl,assist',
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
            // STL specific
            'file' => 'exclude_unless:order_type,stl|required|file|extensions:stl,obj,3mf,STL,OBJ,3MF|max:51200',
            'material_id' => 'exclude_unless:order_type,stl|required|exists:materials,id',
            'color_id' => 'exclude_unless:order_type,stl|nullable|exists:colors,id',
            'volume_cm3' => 'exclude_unless:order_type,stl|required|numeric|min:0.01',
            'quantity' => 'exclude_unless:order_type,stl|required|integer|min:1',
            'infill' => 'exclude_unless:order_type,stl|required|integer|in:10,20,40,60,80,100',
            'quality' => 'exclude_unless:order_type,stl|required|string|in:standard,high,draft',
            'estimated_price' => 'exclude_unless:order_type,stl|required|numeric',
            // Assist specific
            'product_type' => 'exclude_unless:order_type,assist|nullable|string',
            'approx_size' => 'exclude_unless:order_type,assist|nullable|string',
            // Common
            'description' => 'nullable|string',
            'blueprints.*' => 'nullable|file|max:20480'
        ];
    }
}

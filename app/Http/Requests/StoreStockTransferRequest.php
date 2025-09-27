<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreStockTransferRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
        'from_warehouse_id' => 'required|exists:warehouses,id|different:to_warehouse_id',
        'to_warehouse_id'   => 'required|exists:warehouses,id',
        'inventory_item_id' => 'required|exists:inventory_items,id',
        'quantity'          => 'required|integer|min:1',
        ];
    }
}

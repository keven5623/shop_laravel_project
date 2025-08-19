<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RemoveItemCartRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'item_id' => 'required|exists:cart_items,id'
        ];
    }
}

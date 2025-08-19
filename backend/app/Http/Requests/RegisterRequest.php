<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
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
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed', // 需要 password_confirmation
        ];
    }

    public function messages()
    {
        return [
            'name.required' => '請輸入名稱',
            'email.required' => '請輸入 Email',
            'email.unique' => '此 Email 已被註冊',
            'password.required' => '請輸入密碼',
            'password.min' => '密碼至少 6 個字',
            'password.confirmed' => '密碼確認不一致',
        ];
    }
}

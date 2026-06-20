<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class CreateContactRequest extends FormRequest
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            "first_name" => ["required", "string"],
            "last_name" => ["required", "string"],
            "email" => ["required", "email", "unique:contacts,email"],
            "phone" => ["nullable", "string", "regex:/^\+?[\d\s\-\(\)]{7,20}$/"]
        ];
    }
}

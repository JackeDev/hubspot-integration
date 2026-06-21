<?php

namespace App\Http\Requests\Webhook;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateContactRequest extends FormRequest
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
            "event_id" => ["required", "string", "unique:webhook_contact_events,event_id"],
            "contact_id" => ["required", "string", "exists:contacts,client_id"],
            "email" => ["required_without:phone", "email", "unique:contacts,email"],
            "phone" => ["required_without:email", "string", "regex:/^\+?[\d\s\-\(\)]{7,20}$/"]
        ];
    }
}

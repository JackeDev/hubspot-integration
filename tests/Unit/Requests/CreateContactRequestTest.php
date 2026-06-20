<?php

namespace Tests\Unit\Requests;

use App\Http\Requests\CreateContactRequest;
use App\Models\Contact;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class CreateContactRequestTest extends TestCase
{
    use RefreshDatabase;

    private function validate(array $data): \Illuminate\Validation\Validator
    {
        return Validator::make($data, (new CreateContactRequest())->rules());
    }

    private function validData(): array
    {
        return [
            'first_name' => 'John',
            'last_name'  => 'Doe',
            'email'      => 'john@example.com',
            'phone'      => '+1 555 123 4567',
        ];
    }

    public function test_passes_with_all_valid_fields(): void
    {
        $this->assertTrue($this->validate($this->validData())->passes());
    }

    public function test_passes_without_optional_phone(): void
    {
        $data = $this->validData();
        unset($data['phone']);

        $this->assertTrue($this->validate($data)->passes());
    }

    public function test_passes_with_null_phone(): void
    {
        $this->assertTrue($this->validate([...$this->validData(), 'phone' => null])->passes());
    }

    public function test_fails_when_first_name_is_missing(): void
    {
        $data = $this->validData();
        unset($data['first_name']);

        $errors = $this->validate($data)->errors();

        $this->assertTrue($errors->has('first_name'));
    }

    public function test_fails_when_last_name_is_missing(): void
    {
        $data = $this->validData();
        unset($data['last_name']);

        $errors = $this->validate($data)->errors();

        $this->assertTrue($errors->has('last_name'));
    }

    public function test_fails_when_email_is_missing(): void
    {
        $data = $this->validData();
        unset($data['email']);

        $errors = $this->validate($data)->errors();

        $this->assertTrue($errors->has('email'));
    }

    public function test_fails_when_email_format_is_invalid(): void
    {
        $errors = $this->validate([...$this->validData(), 'email' => 'not-an-email'])->errors();

        $this->assertTrue($errors->has('email'));
    }

    public function test_fails_when_email_already_exists(): void
    {
        Contact::create([
            'first_name'      => 'Jane',
            'last_name'       => 'Doe',
            'email'           => 'john@example.com',
            'client_id'       => 'existing-hs-456',
            'client_provider' => 'hubspot',
        ]);

        $errors = $this->validate($this->validData())->errors();

        $this->assertTrue($errors->has('email'));
    }

    public function test_fails_when_phone_format_is_invalid(): void
    {
        $errors = $this->validate([...$this->validData(), 'phone' => 'abc@@##invalid'])->errors();

        $this->assertTrue($errors->has('phone'));
    }

    public function test_passes_with_international_phone_format(): void
    {
        $this->assertTrue($this->validate([...$this->validData(), 'phone' => '+52 55 1234 5678'])->passes());
    }

    public function test_passes_with_local_phone_format(): void
    {
        $this->assertTrue($this->validate([...$this->validData(), 'phone' => '555-123-4567'])->passes());
    }
}

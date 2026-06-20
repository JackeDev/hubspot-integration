<?php

namespace Tests\Unit\Requests;

use App\Http\Requests\CreateContactRequest;
use App\Http\Requests\CreateDealRequest;
use App\Models\Contact;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class CreateDealRequestTest extends TestCase
{
    use RefreshDatabase;

    private function validate(array $data): \Illuminate\Validation\Validator
    {
        return Validator::make($data, (new CreateDealRequest())->rules());
    }

    private function validData(): array
    {
        return [
            'name' => 'Summer Vacation',
            'amount'  => 5000,
            'pipeline' => 'default',
            'stage' => 'qualified',
        ];
    }

    public function test_passes_with_all_valid_fields(): void
    {
        $this->assertTrue($this->validate($this->validData())->passes());
    }

    public function test_fails_when_name_is_missing(): void
    {
        $data = $this->validData();
        unset($data['name']);

        $errors = $this->validate($data)->errors();

        $this->assertTrue($errors->has('name'));
    }

    public function test_fails_when_amount_is_missing(): void
    {
        $data = $this->validData();
        unset($data['amount']);

        $errors = $this->validate($data)->errors();

        $this->assertTrue($errors->has('amount'));
    }

    public function test_fails_when_amount_is_not_valid(): void
    {
        $data = $this->validData();
        $data['amount'] = 0;
        
        $errors = $this->validate($data)->errors();

        $this->assertTrue($errors->has('amount'));
    }

    public function test_fails_when_pipeline_is_missing(): void
    {
        $data = $this->validData();
        unset($data['pipeline']);

        $errors = $this->validate($data)->errors();

        $this->assertTrue($errors->has('pipeline'));
    }

    public function test_fails_when_stage_is_missing(): void
    {
        $data = $this->validData();
        unset($data['stage']);

        $errors = $this->validate($data)->errors();

        $this->assertTrue($errors->has('stage'));
    }
}

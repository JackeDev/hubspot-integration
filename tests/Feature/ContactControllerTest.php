<?php

namespace Tests\Feature;

use App\Models\Contact;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\ConnectionException;
use Tambourine\HubspotClient\Exceptions\AuthorizationException;
use Tambourine\HubspotClient\Exceptions\GenericHubspotException;
use Tambourine\HubspotClient\Exceptions\RateLimitException;
use Tambourine\HubspotClient\Services\HubspotContactService;
use Tests\TestCase;

class ContactControllerTest extends TestCase
{
    use RefreshDatabase;

    private function validPayload(): array
    {
        return [
            'first_name' => 'John',
            'last_name'  => 'Doe',
            'email'      => 'john@example.com',
            'phone'      => '+1 555 123 4567',
        ];
    }

    private function mockHubspot(array $returns = ['id' => 'hs-123']): void
    {
        $this->mock(HubspotContactService::class)
            ->shouldReceive('create')
            ->once()
            ->andReturn($returns);
    }

    public function test_creates_contact_successfully(): void
    {
        $this->mockHubspot();

        $response = $this->postJson('/api/contact', $this->validPayload());

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'id', 
                    'first_name', 
                    'last_name', 
                    'email',
                    'phone', 
                    'client_id', 
                    'client_provider',
                    'last_client_updated',
                    'created_at', 
                    'updated_at',
                ],
            ])
            ->assertJsonPath('data.email', 'john@example.com')
            ->assertJsonPath('data.client_id', 'hs-123')
            ->assertJsonPath('data.client_provider', 'hubspot');

        $this->assertDatabaseHas('contacts', [
            'email'           => 'john@example.com',
            'client_id'       => 'hs-123',
            'client_provider' => 'hubspot',
        ]);
    }

    public function test_creates_contact_without_optional_phone(): void
    {
        $this->mockHubspot();

        $payload = $this->validPayload();
        unset($payload['phone']);

        $this->postJson('/api/contact', $payload)
            ->assertStatus(201)
            ->assertJsonPath('data.phone', null);
    }

    public function test_returns_422_when_first_name_is_missing(): void
    {
        $payload = $this->validPayload();
        unset($payload['first_name']);

        $this->postJson('/api/contact', $payload)
            ->assertStatus(422)
            ->assertJsonValidationErrors(['first_name']);
    }

    public function test_returns_422_when_last_name_is_missing(): void
    {
        $payload = $this->validPayload();
        unset($payload['last_name']);

        $this->postJson('/api/contact', $payload)
            ->assertStatus(422)
            ->assertJsonValidationErrors(['last_name']);
    }

    public function test_returns_422_when_email_is_missing(): void
    {
        $payload = $this->validPayload();
        unset($payload['email']);

        $this->postJson('/api/contact', $payload)
            ->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_returns_422_when_email_format_is_invalid(): void
    {
        $payload          = $this->validPayload();
        $payload['email'] = 'not-a-valid-email';

        $this->postJson('/api/contact', $payload)
            ->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_returns_422_when_email_already_exists(): void
    {
        Contact::create([
            'first_name'      => 'Jane',
            'last_name'       => 'Doe',
            'email'           => 'john@example.com',
            'client_id'       => 'existing-hs-456',
            'client_provider' => 'hubspot',
        ]);

        $this->postJson('/api/contact', $this->validPayload())
            ->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_returns_422_when_phone_format_is_invalid(): void
    {
        $payload          = $this->validPayload();
        $payload['phone'] = 'abc@@##invalid';

        $this->postJson('/api/contact', $payload)
            ->assertStatus(422)
            ->assertJsonValidationErrors(['phone']);
    }

    public function test_returns_401_when_hubspot_token_is_invalid(): void
    {
        $this->mock(HubspotContactService::class)
            ->shouldReceive('create')
            ->once()
            ->andThrow(new AuthorizationException('HubSpot authentication failed', 401));

        $this->postJson('/api/contact', $this->validPayload())
            ->assertStatus(401)
            ->assertJson(['message' => 'HubSpot authentication failed', 'code' => 401]);

        $this->assertDatabaseEmpty('contacts');
    }

    public function test_returns_429_when_hubspot_rate_limit_is_exceeded(): void
    {
        $this->mock(HubspotContactService::class)
            ->shouldReceive('create')
            ->once()
            ->andThrow(new RateLimitException('HubSpot rate limit exceeded', 429));

        $this->postJson('/api/contact', $this->validPayload())
            ->assertStatus(429)
            ->assertJson(['message' => 'HubSpot rate limit exceeded', 'code' => 429]);

        $this->assertDatabaseEmpty('contacts');
    }

    public function test_returns_500_on_generic_hubspot_api_error(): void
    {
        $this->mock(HubspotContactService::class)
            ->shouldReceive('create')
            ->once()
            ->andThrow(new GenericHubspotException('HubSpot API error', 500));

        $this->postJson('/api/contact', $this->validPayload())
            ->assertStatus(500)
            ->assertJson(['message' => 'HubSpot API error', 'code' => 500]);

        $this->assertDatabaseEmpty('contacts');
    }

    public function test_returns_503_when_hubspot_connection_fails(): void
    {
        $this->mock(HubspotContactService::class)
            ->shouldReceive('create')
            ->once()
            ->andThrow(new ConnectionException('Connection refused', 503));

        $this->postJson('/api/contact', $this->validPayload())
            ->assertStatus(503)
            ->assertJsonStructure(['message', 'code']);

        $this->assertDatabaseEmpty('contacts');
    }

    public function test_returns_504_when_hubspot_request_times_out(): void
    {
        $this->mock(HubspotContactService::class)
            ->shouldReceive('create')
            ->once()
            ->andThrow(new ConnectionException('cURL error 28: Operation timed out', 504));

        $this->postJson('/api/contact', $this->validPayload())
            ->assertStatus(504)
            ->assertJsonStructure(['message', 'code']);

        $this->assertDatabaseEmpty('contacts');
    }

    public function test_rolls_back_db_contact_when_hubspot_throws(): void
    {
        $this->mock(HubspotContactService::class)
            ->shouldReceive('create')
            ->once()
            ->andThrow(new GenericHubspotException('HubSpot API error', 500));

        $this->postJson('/api/contact', $this->validPayload())
            ->assertStatus(500)
            ->assertJson(['message' => 'HubSpot API error', 'code' => 500]);

        $this->assertDatabaseEmpty('contacts');
    }
}

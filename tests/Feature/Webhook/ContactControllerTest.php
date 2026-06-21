<?php

namespace Tests\Feature\Webhook;

use App\Models\Contact;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;
use Illuminate\Support\Facades\DB;

class ContactControllerTest extends TestCase
{
    use RefreshDatabase;

    const UPDATE_ROUTE = 'webhooks.contacts.update';

    protected function setUp(): void
    {
        parent::setUp();
        Log::spy();
    }

    private function createContact(string $clientId = 'hs-contact-123'): Contact
    {
        return Contact::create([
            'first_name'      => 'John',
            'last_name'       => 'Doe',
            'email'           => 'john@example.com',
            'client_id'       => $clientId,
            'client_provider' => 'hubspot',
        ]);
    }

    private function validPayload(string $clientId = 'hs-contact-123'): array
    {
        return [
            'event_id'   => 'evt-001',
            'contact_id' => $clientId,
            'phone'      => '+1 555 000 0001',
        ];
    }

    public function test_processes_webhook_and_returns_updated_contact(): void
    {
        $contact = $this->createContact();

        $this->postJson(route(self::UPDATE_ROUTE), $this->validPayload())
            ->assertStatus(Response::HTTP_OK)
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
            ->assertJsonPath('data.client_id', 'hs-contact-123');

        $this->assertDatabaseHas('webhook_contact_events', [
            'event_id'   => 'evt-001',
            'contact_id' => 'hs-contact-123',
        ]);

        $this->assertNotNull($contact->fresh()->last_client_updated);

        Log::shouldNotHaveReceived('error');
    }

    public function test_updates_contact_email_when_provided(): void
    {
        $contact = $this->createContact();

        $this->postJson(route(self::UPDATE_ROUTE), [
            ...$this->validPayload(),
            'email' => 'updated@example.com',
        ])->assertStatus(Response::HTTP_OK);

        $this->assertEquals('updated@example.com', $contact->fresh()->email);

        Log::shouldNotHaveReceived('error');
    }

    public function test_updates_contact_phone_when_provided(): void
    {
        $contact = $this->createContact();

        $this->postJson(route(self::UPDATE_ROUTE), [
            ...$this->validPayload(),
            'phone' => '+1 555 999 0000',
        ])->assertStatus(Response::HTTP_OK);

        $this->assertEquals('+1 555 999 0000', $contact->fresh()->phone);

        Log::shouldNotHaveReceived('error');
    }

    public function test_returns_422_when_event_already_processed(): void
    {
        $this->createContact();

        $this->postJson(route(self::UPDATE_ROUTE), $this->validPayload())
            ->assertStatus(Response::HTTP_OK);

        $this->postJson(route(self::UPDATE_ROUTE), $this->validPayload())
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors(['event_id']);

        $this->assertEquals(1, DB::table('webhook_contact_events')->count());

        Log::shouldNotHaveReceived('error');
    }

    public function test_returns_422_with_validation_error_structure(): void
    {
        $this->postJson(route(self::UPDATE_ROUTE), [])
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonStructure([
                'message',
                'errors' => ['event_id', 'contact_id', 'email', 'phone'],
            ]);

        Log::shouldNotHaveReceived('error');
    }

    public function test_returns_422_when_neither_email_nor_phone_provided(): void
    {
        $this->createContact();

        $this->postJson(route(self::UPDATE_ROUTE), [
            'event_id'   => 'evt-002',
            'contact_id' => 'hs-contact-123',
        ])
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors(['email', 'phone']);

        Log::shouldNotHaveReceived('error');
    }

    public function test_returns_422_when_contact_not_found_by_client_id(): void
    {
        $this->postJson(route(self::UPDATE_ROUTE), [
            'event_id'   => 'evt-001',
            'contact_id' => 'non-existent-hs-id',
            'phone'      => '+1 555 000 0001',
        ])
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors(['contact_id']);

        Log::shouldNotHaveReceived('error');
    }

    public function test_returns_422_when_email_is_already_taken_by_another_contact(): void
    {
        $this->createContact('hs-001');

        Contact::create([
            'first_name'      => 'Jane',
            'last_name'       => 'Smith',
            'email'           => 'jane@example.com',
            'client_id'       => 'hs-002',
            'client_provider' => 'hubspot',
        ]);

        $this->postJson(route(self::UPDATE_ROUTE), [
            'event_id'   => 'evt-001',
            'contact_id' => 'hs-001',
            'email'      => 'jane@example.com',
        ])
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors(['email']);

        Log::shouldNotHaveReceived('error');
    }

    public function test_returns_422_when_phone_format_is_invalid(): void
    {
        $this->createContact();

        $this->postJson(route(self::UPDATE_ROUTE), [
            ...$this->validPayload(),
            'phone' => 'abc@@invalid',
        ])
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors(['phone']);

        Log::shouldNotHaveReceived('error');
    }

    public function test_returns_500_on_unexpected_error(): void
    {
        $this->createContact();

        $this->mock(\App\Services\WebhookContactEventService::class)
            ->shouldReceive('createContactEvent')
            ->once()
            ->andThrow(new \RuntimeException('Database connection lost', 0));

        $this->postJson(route(self::UPDATE_ROUTE), $this->validPayload())
            ->assertStatus(Response::HTTP_INTERNAL_SERVER_ERROR)
            ->assertJson(['message' => 'Unexpected Error']);

        Log::shouldHaveReceived('error')
            ->once()
            ->with('Database connection lost', ['code' => 0]);
    }
}

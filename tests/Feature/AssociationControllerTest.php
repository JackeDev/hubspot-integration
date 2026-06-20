<?php

namespace Tests\Feature;

use App\Models\Contact;
use App\Models\Deal;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Tambourine\HubspotClient\Exceptions\AuthorizationException;
use Tambourine\HubspotClient\Exceptions\GenericHubspotException;
use Tambourine\HubspotClient\Exceptions\RateLimitException;
use Tambourine\HubspotClient\Exceptions\ResourceNotFoundException;
use Tambourine\HubspotClient\Services\HubspotAssociationService;
use Tests\TestCase;

class AssociationControllerTest extends TestCase
{
    use RefreshDatabase;

    const CREATE_ROUTE = 'associations.create';

    const HUBSPOT_ERROR_RESPONSE = ['message' => 'Hubspot Service Error', 'code' => Response::HTTP_SERVICE_UNAVAILABLE];

    protected function setUp(): void
    {
        parent::setUp();
        Log::spy();
        Log::shouldReceive('channel')->andReturnSelf();
    }

    private function createContact(): Contact
    {
        return Contact::create([
            'first_name'      => 'John',
            'last_name'       => 'Doe',
            'email'           => 'john@example.com',
            'client_id'       => 'hs-contact-123',
            'client_provider' => 'hubspot',
        ]);
    }

    private function createDeal(): Deal
    {
        return Deal::create([
            'name'            => 'Test Deal',
            'amount'          => 5000,
            'pipeline'        => 'default',
            'stage'           => 'qualified',
            'client_id'       => 'hs-deal-123',
            'client_provider' => 'hubspot',
        ]);
    }

    private function hubspotResponse(int $status, array $body = []): \Illuminate\Http\Client\Response
    {
        return new \Illuminate\Http\Client\Response(
            new \GuzzleHttp\Psr7\Response($status, ['Content-Type' => 'application/json'], json_encode($body))
        );
    }

    private function mockHubspot(): void
    {
        $this->mock(HubspotAssociationService::class)
            ->shouldReceive('create')
            ->once()
            ->andReturn($this->hubspotResponse(201));
    }

    public function test_creates_association_successfully(): void
    {
        $contact = $this->createContact();
        $deal    = $this->createDeal();

        $this->mockHubspot();

        $this->postJson(route(self::CREATE_ROUTE), [
            'contact_id' => $contact->id,
            'deal_id'    => $deal->id,
        ])
            ->assertStatus(Response::HTTP_CREATED)
            ->assertJsonStructure([
                'data' => ['id', 'contact_id', 'deal_id', 'created_at', 'updated_at'],
            ]);

        $this->assertDatabaseHas('associations', [
            'contact_id' => $contact->id,
            'deal_id'    => $deal->id,
        ]);

        Log::shouldNotHaveReceived('error');
    }

    public function test_returns_422_with_validation_error_structure(): void
    {
        $this->postJson(route(self::CREATE_ROUTE), [])
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonStructure([
                'message',
                'errors' => ['contact_id', 'deal_id'],
            ]);

        Log::shouldNotHaveReceived('error');
    }

    public function test_returns_422_when_contact_does_not_exist_in_db(): void
    {
        $deal = $this->createDeal();

        $this->postJson(route(self::CREATE_ROUTE), [
            'contact_id' => 9999,
            'deal_id'    => $deal->id,
        ])
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors(['contact_id']);

        Log::shouldNotHaveReceived('error');
    }

    public function test_returns_422_when_deal_does_not_exist_in_db(): void
    {
        $contact = $this->createContact();

        $this->postJson(route(self::CREATE_ROUTE), [
            'contact_id' => $contact->id,
            'deal_id'    => 9999,
        ])
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors(['deal_id']);

        Log::shouldNotHaveReceived('error');
    }
    
    public function test_returns_503_when_contact_not_found_in_hubspot(): void
    {
        $contact = $this->createContact();
        $deal    = $this->createDeal();

        $this->mock(HubspotAssociationService::class)
            ->shouldReceive('create')
            ->once()
            ->andThrow(new ResourceNotFoundException('Contact not found in HubSpot', Response::HTTP_NOT_FOUND));

        $this->postJson(route(self::CREATE_ROUTE), [
            'contact_id' => $contact->id,
            'deal_id'    => $deal->id,
        ])
            ->assertStatus(Response::HTTP_SERVICE_UNAVAILABLE)
            ->assertJson(self::HUBSPOT_ERROR_RESPONSE);

        $this->assertDatabaseEmpty('associations');

        Log::shouldHaveReceived('error')
            ->with('Contact not found in HubSpot', ['code' => Response::HTTP_NOT_FOUND]);
    }

    public function test_returns_503_when_deal_not_found_in_hubspot(): void
    {
        $contact = $this->createContact();
        $deal    = $this->createDeal();

        $this->mock(HubspotAssociationService::class)
            ->shouldReceive('create')
            ->once()
            ->andThrow(new ResourceNotFoundException('Deal not found in HubSpot', Response::HTTP_NOT_FOUND));

        $this->postJson(route(self::CREATE_ROUTE), [
            'contact_id' => $contact->id,
            'deal_id'    => $deal->id,
        ])
            ->assertStatus(Response::HTTP_SERVICE_UNAVAILABLE)
            ->assertJson(self::HUBSPOT_ERROR_RESPONSE);

        $this->assertDatabaseEmpty('associations');

        Log::shouldHaveReceived('error')
            ->with('Deal not found in HubSpot', ['code' => Response::HTTP_NOT_FOUND]);
    }

    public function test_returns_503_when_hubspot_token_is_invalid(): void
    {
        $contact = $this->createContact();
        $deal    = $this->createDeal();

        $this->mock(HubspotAssociationService::class)
            ->shouldReceive('create')
            ->once()
            ->andThrow(new AuthorizationException('HubSpot authentication failed', Response::HTTP_UNAUTHORIZED));

        $this->postJson(route(self::CREATE_ROUTE), [
            'contact_id' => $contact->id,
            'deal_id'    => $deal->id,
        ])
            ->assertStatus(Response::HTTP_SERVICE_UNAVAILABLE)
            ->assertJson(self::HUBSPOT_ERROR_RESPONSE);

        $this->assertDatabaseEmpty('associations');

        Log::shouldHaveReceived('error')
            ->with('HubSpot authentication failed', ['code' => Response::HTTP_UNAUTHORIZED]);
    }

    public function test_returns_503_when_hubspot_rate_limit_is_exceeded(): void
    {
        $contact = $this->createContact();
        $deal    = $this->createDeal();

        $this->mock(HubspotAssociationService::class)
            ->shouldReceive('create')
            ->once()
            ->andThrow(new RateLimitException('HubSpot rate limit exceeded', Response::HTTP_TOO_MANY_REQUESTS));

        $this->postJson(route(self::CREATE_ROUTE), [
            'contact_id' => $contact->id,
            'deal_id'    => $deal->id,
        ])
            ->assertStatus(Response::HTTP_SERVICE_UNAVAILABLE)
            ->assertJson(self::HUBSPOT_ERROR_RESPONSE);

        $this->assertDatabaseEmpty('associations');

        Log::shouldHaveReceived('error')
            ->with('HubSpot rate limit exceeded', ['code' => Response::HTTP_TOO_MANY_REQUESTS]);
    }

    public function test_returns_503_when_generic_hubspot_error(): void
    {
        $contact = $this->createContact();
        $deal    = $this->createDeal();

        $this->mock(HubspotAssociationService::class)
            ->shouldReceive('create')
            ->once()
            ->andThrow(new GenericHubspotException('HubSpot API error', Response::HTTP_INTERNAL_SERVER_ERROR));

        $this->postJson(route(self::CREATE_ROUTE), [
            'contact_id' => $contact->id,
            'deal_id'    => $deal->id,
        ])
            ->assertStatus(Response::HTTP_SERVICE_UNAVAILABLE)
            ->assertJson(self::HUBSPOT_ERROR_RESPONSE);

        $this->assertDatabaseEmpty('associations');

        Log::shouldHaveReceived('error')
            ->with('HubSpot API error', ['code' => Response::HTTP_INTERNAL_SERVER_ERROR]);
    }

    public function test_returns_503_when_hubspot_connection_fails(): void
    {
        $contact = $this->createContact();
        $deal    = $this->createDeal();

        $this->mock(HubspotAssociationService::class)
            ->shouldReceive('create')
            ->once()
            ->andThrow(new ConnectionException('Connection refused', Response::HTTP_SERVICE_UNAVAILABLE));

        $this->postJson(route(self::CREATE_ROUTE), [
            'contact_id' => $contact->id,
            'deal_id'    => $deal->id,
        ])
            ->assertStatus(Response::HTTP_SERVICE_UNAVAILABLE)
            ->assertJson(self::HUBSPOT_ERROR_RESPONSE);

        $this->assertDatabaseEmpty('associations');

        Log::shouldHaveReceived('error')
            ->once()
            ->with('Connection refused', ['code' => Response::HTTP_SERVICE_UNAVAILABLE]);
    }

    public function test_returns_503_when_hubspot_request_times_out(): void
    {
        $contact = $this->createContact();
        $deal    = $this->createDeal();

        $this->mock(HubspotAssociationService::class)
            ->shouldReceive('create')
            ->once()
            ->andThrow(new ConnectionException('cURL error 28: Operation timed out', Response::HTTP_GATEWAY_TIMEOUT));

        $this->postJson(route(self::CREATE_ROUTE), [
            'contact_id' => $contact->id,
            'deal_id'    => $deal->id,
        ])
            ->assertStatus(Response::HTTP_SERVICE_UNAVAILABLE)
            ->assertJson(self::HUBSPOT_ERROR_RESPONSE);

        $this->assertDatabaseEmpty('associations');

        Log::shouldHaveReceived('error')
            ->once()
            ->with('cURL error 28: Operation timed out', ['code' => Response::HTTP_GATEWAY_TIMEOUT]);
    }
}

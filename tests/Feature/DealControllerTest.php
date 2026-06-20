<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Tambourine\HubspotClient\Exceptions\AuthorizationException;
use Tambourine\HubspotClient\Exceptions\GenericHubspotException;
use Tambourine\HubspotClient\Exceptions\RateLimitException;
use Tambourine\HubspotClient\Services\HubspotDealService;
use Tests\TestCase;

class DealControllerTest extends TestCase
{
    use RefreshDatabase;

    const CREATE_ROUTE = 'deals.create';

    const HUBSPOT_ERROR_RESPONSE = ['message' => 'Hubspot Service Error', 'code' => Response::HTTP_SERVICE_UNAVAILABLE];

    protected function setUp(): void
    {
        parent::setUp();
        Log::spy();
        Log::shouldReceive('channel')->andReturnSelf();
    }

    private function validPayload(): array
    {
        return [
            'name'     => 'Summer Vacation',
            'amount'   => 5000,
            'pipeline' => 'default',
            'stage'    => 'qualified',
        ];
    }

    private function mockHubspot(array $returns = ['id' => 'hs-deal-123']): void
    {
        $this->mock(HubspotDealService::class)
            ->shouldReceive('create')
            ->once()
            ->andReturn($returns);
    }

    public function test_creates_deal_successfully(): void
    {
        $this->mockHubspot();

        $response = $this->postJson(route(self::CREATE_ROUTE), $this->validPayload());

        $response->assertStatus(Response::HTTP_CREATED)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'name',
                    'amount',
                    'pipeline',
                    'stage',
                    'client_id',
                    'client_provider',
                    'last_client_updated',
                    'created_at',
                    'updated_at',
                ],
            ])
            ->assertJsonPath('data.name', 'Summer Vacation')
            ->assertJsonPath('data.client_id', 'hs-deal-123')
            ->assertJsonPath('data.client_provider', 'hubspot');

        $this->assertDatabaseHas('deals', [
            'name'            => 'Summer Vacation',
            'client_id'       => 'hs-deal-123',
            'client_provider' => 'hubspot',
        ]);

        Log::shouldNotHaveReceived('error');
    }

    public function test_returns_422_with_validation_error_structure(): void
    {
        $this->postJson(route(self::CREATE_ROUTE), [])
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonStructure([
                'message',
                'errors' => [
                    'name',
                    'amount',
                    'pipeline',
                    'stage',
                ],
            ]);

        Log::shouldNotHaveReceived('error');
    }

    public function test_returns_401_when_hubspot_token_is_invalid(): void
    {
        $this->mock(HubspotDealService::class)
            ->shouldReceive('create')
            ->once()
            ->andThrow(new AuthorizationException('HubSpot authentication failed', Response::HTTP_UNAUTHORIZED));

        $this->postJson(route(self::CREATE_ROUTE), $this->validPayload())
            ->assertStatus(Response::HTTP_SERVICE_UNAVAILABLE)
            ->assertJson(self::HUBSPOT_ERROR_RESPONSE);

        $this->assertDatabaseEmpty('deals');

        Log::shouldHaveReceived('error')
            ->with('HubSpot authentication failed', ['code' => Response::HTTP_UNAUTHORIZED]);
    }

    public function test_returns_429_when_hubspot_rate_limit_is_exceeded(): void
    {
        $this->mock(HubspotDealService::class)
            ->shouldReceive('create')
            ->once()
            ->andThrow(new RateLimitException('HubSpot rate limit exceeded', Response::HTTP_TOO_MANY_REQUESTS));

        $this->postJson(route(self::CREATE_ROUTE), $this->validPayload())
            ->assertStatus(Response::HTTP_SERVICE_UNAVAILABLE)
            ->assertJson(self::HUBSPOT_ERROR_RESPONSE);

        $this->assertDatabaseEmpty('deals');

        Log::shouldHaveReceived('error')
            ->with('HubSpot rate limit exceeded', ['code' => Response::HTTP_TOO_MANY_REQUESTS]);
    }

    public function test_returns_500_on_generic_hubspot_api_error(): void
    {
        $this->mock(HubspotDealService::class)
            ->shouldReceive('create')
            ->once()
            ->andThrow(new GenericHubspotException('HubSpot API error', Response::HTTP_INTERNAL_SERVER_ERROR));

        $this->postJson(route(self::CREATE_ROUTE), $this->validPayload())
            ->assertStatus(Response::HTTP_SERVICE_UNAVAILABLE)
            ->assertJson(self::HUBSPOT_ERROR_RESPONSE);

        $this->assertDatabaseEmpty('deals');

        Log::shouldHaveReceived('error')
            ->with('HubSpot API error', ['code' => Response::HTTP_INTERNAL_SERVER_ERROR]);
    }

    public function test_returns_503_when_hubspot_connection_fails(): void
    {
        $this->mock(HubspotDealService::class)
            ->shouldReceive('create')
            ->once()
            ->andThrow(new ConnectionException('Connection refused', Response::HTTP_SERVICE_UNAVAILABLE));

        $this->postJson(route(self::CREATE_ROUTE), $this->validPayload())
            ->assertStatus(Response::HTTP_SERVICE_UNAVAILABLE)
            ->assertJson(self::HUBSPOT_ERROR_RESPONSE);

        $this->assertDatabaseEmpty('deals');

        Log::shouldHaveReceived('error')
            ->once()
            ->with('Connection refused', ['code' => Response::HTTP_SERVICE_UNAVAILABLE]);
    }

    public function test_returns_504_when_hubspot_request_times_out(): void
    {
        $this->mock(HubspotDealService::class)
            ->shouldReceive('create')
            ->once()
            ->andThrow(new ConnectionException('cURL error 28: Operation timed out', Response::HTTP_GATEWAY_TIMEOUT));

        $this->postJson(route(self::CREATE_ROUTE), $this->validPayload())
            ->assertStatus(Response::HTTP_SERVICE_UNAVAILABLE)
            ->assertJson(self::HUBSPOT_ERROR_RESPONSE);

        $this->assertDatabaseEmpty('deals');

        Log::shouldHaveReceived('error')
            ->once()
            ->with('cURL error 28: Operation timed out', ['code' => Response::HTTP_GATEWAY_TIMEOUT]);
    }

    public function test_rolls_back_db_deal_when_hubspot_throws(): void
    {
        $this->mock(HubspotDealService::class)
            ->shouldReceive('create')
            ->once()
            ->andThrow(new GenericHubspotException('HubSpot API error', Response::HTTP_INTERNAL_SERVER_ERROR));

        $this->postJson(route(self::CREATE_ROUTE), $this->validPayload())
            ->assertStatus(Response::HTTP_SERVICE_UNAVAILABLE)
            ->assertJson(self::HUBSPOT_ERROR_RESPONSE);

        $this->assertDatabaseEmpty('deals');

        Log::shouldHaveReceived('error')
            ->with('HubSpot API error', ['code' => Response::HTTP_INTERNAL_SERVER_ERROR]);
    }
}
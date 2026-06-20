<?php

namespace App\OpenApi\Endpoints;

use OpenApi\Attributes as OA;

class DealEndpoints
{
    #[OA\Post(
        path: '/deals',
        summary: 'Create a new deal',
        description: 'Creates a deal in the local database and syncs it with HubSpot within a single transaction. If the HubSpot sync fails, the database record is rolled back and no deal is persisted.',
        tags: ['Deals'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/CreateDealRequest')
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Deal created and synced with HubSpot successfully',
                content: new OA\JsonContent(ref: '#/components/schemas/DealResource')
            ),
            new OA\Response(
                response: 422,
                description: 'Request validation failed (missing or invalid fields)',
                content: new OA\JsonContent(ref: '#/components/schemas/DealValidationErrorResponse')
            ),
            new OA\Response(
                response: 503,
                description: 'HubSpot service error — covers authentication failure, rate limit exceeded, invalid properties, connection failure, and request timeout',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')
            ),
            new OA\Response(
                response: 500,
                description: 'Unexpected internal server error',
                content: new OA\JsonContent(ref: '#/components/schemas/UnexpectedErrorResponse')
            ),
        ]
    )]
    public function store(): void {}
}

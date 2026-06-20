<?php

namespace App\OpenApi\Endpoints;

use OpenApi\Attributes as OA;

class ContactEndpoints
{
    #[OA\Post(
        path: '/contacts',
        summary: 'Create a new contact',
        description: 'Creates a contact in the local database and syncs it with HubSpot within a single transaction. If the HubSpot sync fails, the database record is rolled back and no contact is persisted.',
        tags: ['Contacts'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/CreateContactRequest')
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Contact created and synced with HubSpot successfully',
                content: new OA\JsonContent(ref: '#/components/schemas/ContactResource')
            ),
            new OA\Response(
                response: 422,
                description: 'Request validation failed (missing or invalid fields)',
                content: new OA\JsonContent(ref: '#/components/schemas/ValidationErrorResponse')
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

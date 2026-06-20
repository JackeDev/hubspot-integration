<?php

namespace App\OpenApi\Endpoints;

use OpenApi\Attributes as OA;

class ContactEndpoints
{
    #[OA\Post(
        path: '/contact',
        summary: 'Create a new contact',
        description: 'Creates a contact locally and syncs it with HubSpot. The operation is atomic: if the HubSpot sync fails the database record is rolled back.',
        tags: ['Contacts'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/CreateContactRequest')
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Contact created and synced with HubSpot',
                content: new OA\JsonContent(ref: '#/components/schemas/ContactResource')
            ),
            new OA\Response(
                response: 422,
                description: 'Validation error',
                content: new OA\JsonContent(ref: '#/components/schemas/ValidationErrorResponse')
            ),
            new OA\Response(
                response: 401,
                description: 'HubSpot authentication failed',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')
            ),
            new OA\Response(
                response: 429,
                description: 'HubSpot rate limit exceeded',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')
            ),
            new OA\Response(
                response: 500,
                description: 'HubSpot API error or internal server error',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')
            ),
            new OA\Response(
                response: 503,
                description: 'HubSpot connection failed',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')
            ),
            new OA\Response(
                response: 504,
                description: 'HubSpot request timed out',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')
            ),
        ]
    )]
    public function store(): void {}
}

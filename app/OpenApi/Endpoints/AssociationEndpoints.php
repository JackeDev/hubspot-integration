<?php

namespace App\OpenApi\Endpoints;

use OpenApi\Attributes as OA;

class AssociationEndpoints
{
    #[OA\Post(
        path: '/associations',
        summary: 'Associate a contact with a deal',
        description: 'Links an existing local contact to an existing local deal by creating the association in HubSpot and persisting it locally. Both the contact and deal must already exist in HubSpot (i.e. have a client_id). If either is not found in HubSpot, the operation is rejected.',
        tags: ['Associations'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/CreateAssociationRequest')
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Association created successfully',
                content: new OA\JsonContent(ref: '#/components/schemas/AssociationResource')
            ),
            new OA\Response(
                response: 422,
                description: 'Validation failed — contact_id or deal_id are missing, non-numeric, or do not exist in the local database',
                content: new OA\JsonContent(ref: '#/components/schemas/AssociationValidationErrorResponse')
            ),
            new OA\Response(
                response: 503,
                description: 'HubSpot service error — covers contact/deal not found in HubSpot, authentication failure, rate limit exceeded, connection failure, and request timeout',
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

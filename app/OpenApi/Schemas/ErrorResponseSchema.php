<?php

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'ErrorResponse',
    properties: [
        new OA\Property(property: 'message', type: 'string', example: 'HubSpot authentication failed'),
        new OA\Property(property: 'code',    type: 'integer', example: 401),
    ]
)]
class ErrorResponseSchema {}

#[OA\Schema(
    schema: 'ValidationErrorResponse',
    properties: [
        new OA\Property(property: 'message', type: 'string', example: 'The first_name field is required.'),
        new OA\Property(
            property: 'errors',
            type: 'object',
            example: ['first_name' => ['The first_name field is required.']],
        ),
    ]
)]
class ValidationErrorResponseSchema {}

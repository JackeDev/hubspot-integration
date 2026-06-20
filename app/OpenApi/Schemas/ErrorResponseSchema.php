<?php

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'ErrorResponse',
    properties: [
        new OA\Property(property: 'message', type: 'string', example: 'Hubspot Service Error'),
        new OA\Property(property: 'code',    type: 'integer', example: 503),
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

#[OA\Schema(
    schema: 'UnexpectedErrorResponse',
    properties: [
        new OA\Property(property: 'message', type: 'string', example: 'Unexpected Error'),
        new OA\Property(property: 'code',    type: 'integer', example: 500),
    ]
)]
class UnexpectedErrorResponseSchema {}

#[OA\Schema(
    schema: 'DealValidationErrorResponse',
    properties: [
        new OA\Property(property: 'message', type: 'string', example: 'The name field is required.'),
        new OA\Property(
            property: 'errors',
            type: 'object',
            example: ['name' => ['The name field is required.'], 'amount' => ['The amount field is required.']],
        ),
    ]
)]
class DealValidationErrorResponseSchema {}

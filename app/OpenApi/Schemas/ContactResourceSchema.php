<?php

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'ContactResource',
    properties: [
        new OA\Property(property: 'data', ref: '#/components/schemas/Contact'),
    ]
)]
class ContactResourceSchema {}

#[OA\Schema(
    schema: 'Contact',
    properties: [
        new OA\Property(property: 'id',                  type: 'integer', example: 1),
        new OA\Property(property: 'first_name',          type: 'string',  example: 'John'),
        new OA\Property(property: 'last_name',           type: 'string',  example: 'Doe'),
        new OA\Property(property: 'email',               type: 'string',  format: 'email', example: 'john@example.com'),
        new OA\Property(property: 'phone',               type: 'string',  nullable: true,  example: '+1 555 123 4567'),
        new OA\Property(property: 'client_id',           type: 'string',  example: 'hs-123'),
        new OA\Property(property: 'client_provider',     type: 'string',  example: 'hubspot'),
        new OA\Property(property: 'last_client_updated', type: 'string',  format: 'date-time', nullable: true),
        new OA\Property(property: 'created_at',          type: 'string',  format: 'date-time'),
        new OA\Property(property: 'updated_at',          type: 'string',  format: 'date-time'),
    ]
)]
class ContactSchema {}

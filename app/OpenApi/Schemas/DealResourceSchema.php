<?php

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'DealResource',
    properties: [
        new OA\Property(property: 'data', ref: '#/components/schemas/Deal'),
    ]
)]
class DealResourceSchema {}

#[OA\Schema(
    schema: 'Deal',
    properties: [
        new OA\Property(property: 'id',                  type: 'integer', example: 1),
        new OA\Property(property: 'name',                type: 'string',  example: 'Summer Vacation'),
        new OA\Property(property: 'amount',              type: 'number',  format: 'float', example: 5000),
        new OA\Property(property: 'pipeline',            type: 'string',  example: 'default'),
        new OA\Property(property: 'stage',               type: 'string',  example: 'appointmentscheduled'),
        new OA\Property(property: 'client_id',           type: 'string',  example: 'hs-deal-123'),
        new OA\Property(property: 'client_provider',     type: 'string',  example: 'hubspot'),
        new OA\Property(property: 'last_client_updated', type: 'string',  format: 'date-time', nullable: true),
        new OA\Property(property: 'created_at',          type: 'string',  format: 'date-time'),
        new OA\Property(property: 'updated_at',          type: 'string',  format: 'date-time'),
    ]
)]
class DealSchema {}

<?php

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'CreateDealRequest',
    required: ['name', 'amount', 'pipeline', 'stage'],
    properties: [
        new OA\Property(property: 'name',     type: 'string',  example: 'Summer Vacation'),
        new OA\Property(property: 'amount',   type: 'number',  format: 'float', minimum: 1, example: 5000),
        new OA\Property(property: 'pipeline', type: 'string',  example: 'default'),
        new OA\Property(property: 'stage',    type: 'string',  example: 'appointmentscheduled'),
    ]
)]
class CreateDealSchema {}

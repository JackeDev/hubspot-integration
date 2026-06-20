<?php

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'AssociationResource',
    properties: [
        new OA\Property(property: 'data', ref: '#/components/schemas/Association'),
    ]
)]
class AssociationResourceSchema {}

#[OA\Schema(
    schema: 'Association',
    properties: [
        new OA\Property(property: 'id',         type: 'integer', example: 1),
        new OA\Property(property: 'contact_id', type: 'integer', example: 5),
        new OA\Property(property: 'deal_id',    type: 'integer', example: 3),
        new OA\Property(property: 'created_at', type: 'string',  format: 'date-time'),
        new OA\Property(property: 'updated_at', type: 'string',  format: 'date-time'),
    ]
)]
class AssociationSchema {}

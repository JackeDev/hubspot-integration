<?php

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'CreateAssociationRequest',
    required: ['contact_id', 'deal_id'],
    properties: [
        new OA\Property(property: 'contact_id', type: 'integer', description: 'ID of an existing local contact', example: 5),
        new OA\Property(property: 'deal_id',    type: 'integer', description: 'ID of an existing local deal',    example: 3),
    ]
)]
class CreateAssociationSchema {}

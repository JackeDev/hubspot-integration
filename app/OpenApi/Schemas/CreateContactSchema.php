<?php

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'CreateContactRequest',
    required: ['first_name', 'last_name', 'email'],
    properties: [
        new OA\Property(property: 'first_name', type: 'string', example: 'John'),
        new OA\Property(property: 'last_name',  type: 'string', example: 'Doe'),
        new OA\Property(property: 'email',      type: 'string', format: 'email', example: 'john@example.com'),
        new OA\Property(property: 'phone',      type: 'string', nullable: true, example: '+1 555 123 4567'),
    ]
)]
class CreateContactSchema {}

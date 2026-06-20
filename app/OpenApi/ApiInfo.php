<?php

namespace App\OpenApi;

use OpenApi\Attributes as OA;

#[OA\Info(
    version: '1.0.0',
    title: 'Tambourine HubSpot Integration API',
    description: 'API for managing contacts and deals synced with HubSpot.',
)]
#[OA\Server(url: '/api', description: 'API Server')]
#[OA\Tag(name: 'Contacts', description: 'Contact management endpoints')]
class ApiInfo {}

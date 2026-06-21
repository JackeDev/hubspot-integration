<?php

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'WebhookContactUpdateRequest',
    required: ['event_id', 'contact_id'],
    properties: [
        new OA\Property(
            property: 'event_id',
            type: 'string',
            description: 'Unique identifier for this webhook event. Used to guarantee idempotency — duplicate event IDs are rejected.',
            example: 'evt-abc123'
        ),
        new OA\Property(
            property: 'contact_id',
            type: 'string',
            description: 'The HubSpot contact ID (client_id) of the contact to update. Must match an existing contact.',
            example: 'hs-contact-123'
        ),
        new OA\Property(
            property: 'email',
            type: 'string',
            format: 'email',
            nullable: true,
            description: 'New email address. Required if phone is not provided. Must be unique across all contacts.',
            example: 'updated@example.com'
        ),
        new OA\Property(
            property: 'phone',
            type: 'string',
            nullable: true,
            description: 'New phone number. Required if email is not provided. Must match international phone format.',
            example: '+1 555 123 4567'
        ),
    ]
)]
class WebhookContactUpdateSchema {}

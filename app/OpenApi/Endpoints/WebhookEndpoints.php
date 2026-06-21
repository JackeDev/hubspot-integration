<?php

namespace App\OpenApi\Endpoints;

use OpenApi\Attributes as OA;

class WebhookEndpoints
{
    #[OA\Post(
        path: '/webhooks/contact-updated',
        summary: 'Receive a HubSpot contact-updated event',
        description: <<<'DESC'
Processes an incoming HubSpot webhook event for a contact update.

**Idempotency:** Each `event_id` is accepted only once. Replaying the same event returns 422.

**Field requirements:** At least one of `email` or `phone` must be provided. Both can be sent together.

On success, the matching contact (looked up by `contact_id` = HubSpot `client_id`) is updated with the provided fields and `last_client_updated` is set to the current timestamp. The processed event is recorded and the updated contact is returned.
DESC,
        tags: ['Webhooks'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/WebhookContactUpdateRequest')
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Event processed successfully — returns the updated contact',
                content: new OA\JsonContent(ref: '#/components/schemas/ContactResource')
            ),
            new OA\Response(
                response: 422,
                description: 'Validation failed — missing required fields, duplicate event_id, unknown contact_id, duplicate email, or invalid phone format',
                content: new OA\JsonContent(ref: '#/components/schemas/WebhookContactUpdateValidationErrorResponse')
            ),
            new OA\Response(
                response: 500,
                description: 'Unexpected internal server error',
                content: new OA\JsonContent(ref: '#/components/schemas/UnexpectedErrorResponse')
            ),
        ]
    )]
    public function update(): void {}
}

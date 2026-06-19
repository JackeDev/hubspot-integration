<?php

namespace App\Services\Hubspot;

use App\Services\Hubspot\Contracts\HubspotServiceInterface;

class HubspotServices extends HubspotClient implements HubspotServiceInterface
{
    public function createContact(array $data): array
    {
        $payload = [
            'properties' => [
                'firstname' => $data['firstname'],
                /*'lastname'  => $data['lastname'],
                'email'     => $data['email'],
                'phone'     => $data['phone'] ?? null,*/
            ]
        ];

        return $this->request('POST', '/contacts', $payload)->json();
    }
}

<?php

namespace App\Services\Hubspot\Contracts;

interface HubspotServiceInterface
{
    public function createContact(array $data): array;
}

<?php

namespace App\Services;

use App\Models\Contact;
use App\Repositories\ContactRepository;
use Illuminate\Support\Facades\DB;
use Tambourine\HubspotClient\Services\HubspotContactService;

class ContactService
{
    public function __construct(
        protected ContactRepository $contactRepository,
        protected HubspotContactService $hubspotContactService
    ) {}

    public function createContact(array $data): Contact
    {
        return DB::transaction(function () use ($data) {
            $contact = $this->contactRepository->create($data);

            $hubspotResult = $this->hubspotContactService->create($data);

            return $this->contactRepository->update($contact, [
                'client_id'       => $hubspotResult['id'],
                'client_provider' => 'hubspot',
            ]);
        });
    }
}

<?php

namespace App\Services;

use App\Models\Contact;
use App\Repositories\ContactRepository;
use Illuminate\Http\Response;
use Tambourine\HubspotClient\Exceptions\GenericHubspotException;
use Tambourine\HubspotClient\Services\HubspotContactService;

class ContactService
{
    public function __construct(
        protected ContactRepository $contactRepository,
        protected HubspotContactService $hubspotContactService
    ) {}

    public function getContactById(int $id): Contact
    {
        return $this->contactRepository->getById($id);
    }

    public function createContact(array $data): Contact
    {
        $hubspotResult = $this->hubspotContactService->create($data);

        if ($hubspotResult->status() === Response::HTTP_CREATED){
            $data = [
                ...$data,
                'client_id'       => $hubspotResult['id'],
                'client_provider' => 'hubspot',
            ];

            $contact = $this->contactRepository->create($data);

            return $contact;
        }
        
        throw new GenericHubspotException(code: $hubspotResult->status(), response: $hubspotResult->json());
    }
}

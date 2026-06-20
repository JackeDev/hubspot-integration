<?php

namespace App\Services;

use App\Models\Association;
use App\Repositories\AssociationRepository;
use Tambourine\HubspotClient\Services\HubspotAssociationService;
use Illuminate\Http\Response;
use Tambourine\HubspotClient\Exceptions\GenericHubspotException;

class AssociationService
{
    public function __construct(
        protected AssociationRepository $associationRepository,
        protected ContactService $contactService,
        protected DealService $dealService,
        protected HubspotAssociationService $hubspotAssociationService
    ) {}

    public function createAssociation(array $data): Association
    {
        $contact = $this->contactService->getContactById($data['contact_id']);
        $deal = $this->dealService->getDealById($data['deal_id']);

         $hubspotData = [
            'contact_id' => $contact->client_id,
            'deal_id' => $deal->client_id
        ];

        $hubspotResult = $this->hubspotAssociationService->create($hubspotData);

        if ($hubspotResult->status() === Response::HTTP_CREATED){
            return $this->associationRepository->create($data);
        }
        
        throw new GenericHubspotException(code: $hubspotResult->status(), response: $hubspotResult->json());
    }
}

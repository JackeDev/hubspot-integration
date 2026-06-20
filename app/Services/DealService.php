<?php

namespace App\Services;

use App\Models\Deal;
use App\Repositories\DealRepository;
use Tambourine\HubspotClient\Services\HubspotDealService;
use Tambourine\HubspotClient\Exceptions\GenericHubspotException;
use Illuminate\Http\Response;

class DealService
{
    public function __construct(
        protected DealRepository $dealRepository,
        protected HubspotDealService $hubspotDealService
    ) {}

    public function getDealById(int $id): Deal
    {
        return $this->dealRepository->getById($id);
    }

    public function createDeal(array $data): Deal
    {
        $hubspotResult = $this->hubspotDealService->create($data);

        if ($hubspotResult->status() === Response::HTTP_CREATED){
            $data = [
                ...$data,
                'client_id'       => $hubspotResult['id'],
                'client_provider' => 'hubspot',
            ];

            $deal = $this->dealRepository->create($data);

            return $deal;
        }
        
        throw new GenericHubspotException(code: $hubspotResult->status(), response: $hubspotResult->json());
    }
}

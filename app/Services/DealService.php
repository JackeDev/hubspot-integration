<?php

namespace App\Services;

use App\Models\Deal;
use Illuminate\Support\Facades\DB;
use App\Repositories\DealRepository;
use Tambourine\HubspotClient\Services\HubspotDealService;

class DealService
{
    public function __construct(
        protected DealRepository $dealRepository,
        protected HubspotDealService $hubspotDealService
    ) {}

    public function createDeal(array $data): Deal
    {
        return DB::transaction(function () use ($data) {
            $deal = $this->dealRepository->create($data);

            $hubspotResult = $this->hubspotDealService->create($data);

            return $this->dealRepository->update($deal, [
                'client_id'       => $hubspotResult['id'],
                'client_provider' => 'hubspot',
            ]);
        });
    }
}

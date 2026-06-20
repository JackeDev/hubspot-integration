<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateDealRequest;
use App\Http\Resources\DealResource;
use App\Services\DealService;
use App\Traits\ErrorResponses;
use Exception;

class DealController extends Controller
{
    use ErrorResponses;

    public function __construct(protected DealService $dealService) {}
    
    public function store(CreateDealRequest $request)
    {
        try {
            $deal = $this->dealService->createDeal($request->all());
            $deal->refresh();
            return (new DealResource($deal))->response()->setStatusCode(201);
        } catch (Exception $exception) {
            $this->error($exception->getMessage(), $exception->getCode());
        }
    }
}

<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateDealRequest;
use App\Http\Resources\DealResource;
use App\Services\DealService;
use App\Traits\HandleExceptions;
use Exception;
use Illuminate\Http\Client\ConnectionException;
use Tambourine\HubspotClient\Exceptions\AuthorizationException;
use Illuminate\Http\Response;
use Tambourine\HubspotClient\Exceptions\GenericHubspotException;
use Tambourine\HubspotClient\Exceptions\RateLimitException;
use Tambourine\HubspotClient\Exceptions\ValidationException;

class DealController extends Controller
{
    use HandleExceptions;

    public function __construct(protected DealService $dealService) {}
    
    public function store(CreateDealRequest $request)
    {
        try {
            $deal = $this->dealService->createDeal($request->all());
            $deal->refresh();
            return (new DealResource($deal))->response()->setStatusCode(Response::HTTP_CREATED);
        } catch (
            AuthorizationException | 
            RateLimitException | 
            ValidationException | 
            GenericHubspotException |
            ConnectionException $exception
        )  
        {
            return $this->handleErrorResponse($exception, "Hubspot Service Error", Response::HTTP_SERVICE_UNAVAILABLE);    
        }
        catch (Exception $exception) {
            return $this->handleErrorResponse($exception, "Unexpected Error");
        }
    }
}

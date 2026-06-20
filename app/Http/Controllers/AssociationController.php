<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateAsociationRequest;
use App\Services\AssociationService;
use App\Traits\HandleExceptions;
use Exception;
use Illuminate\Http\Client\ConnectionException;
use Tambourine\HubspotClient\Exceptions\AuthorizationException;
use Illuminate\Http\Response;
use Tambourine\HubspotClient\Exceptions\GenericHubspotException;
use Tambourine\HubspotClient\Exceptions\RateLimitException;
use Tambourine\HubspotClient\Exceptions\ValidationException;
use App\Http\Resources\AssociationResource;
use Tambourine\HubspotClient\Exceptions\ResourceNotFoundException;

class AssociationController extends Controller
{
    use HandleExceptions;

    public function __construct(protected AssociationService $associationService) {}
    
    public function store(CreateAsociationRequest $request)
    {
        try {
            $association = $this->associationService->createAssociation($request->validated());
            $association->refresh();
            return new AssociationResource($association);
        } 
        catch (
            AuthorizationException | 
            RateLimitException | 
            ValidationException | 
            GenericHubspotException |
            ConnectionException |
            ResourceNotFoundException $exception
        )  
        {
            return $this->handleErrorResponse($exception, "Hubspot Service Error", Response::HTTP_SERVICE_UNAVAILABLE);    
        }
        catch (Exception $exception) {
            return $this->handleErrorResponse($exception, "Unexpected Error");
        }
    }
}

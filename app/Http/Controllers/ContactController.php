<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateContactRequest;
use App\Http\Resources\ContactResource;
use App\Services\ContactService;
use App\Traits\HandleExceptions;
use Exception;
use Illuminate\Http\Client\ConnectionException;
use Tambourine\HubspotClient\Exceptions\AuthorizationException;
use Illuminate\Http\Response;
use Tambourine\HubspotClient\Exceptions\GenericHubspotException;
use Tambourine\HubspotClient\Exceptions\RateLimitException;
use Tambourine\HubspotClient\Exceptions\ValidationException;

class ContactController extends Controller
{
    use HandleExceptions;

    public function __construct(protected ContactService $contactService) {}

    public function store(CreateContactRequest $request)
    {
        try {
            $contact = $this->contactService->createContact($request->validated());
            $contact->refresh();
            return (new ContactResource($contact))->response()->setStatusCode(Response::HTTP_CREATED);
        } 
        catch (
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

<?php

namespace App\Http\Controllers\Webhook;

use App\Http\Resources\ContactResource;
use App\Traits\HandleExceptions;
use Exception;
use App\Http\Controllers\Controller;
use App\Http\Requests\Webhook\UpdateContactRequest;
use App\Services\WebhookContactEventService;

class ContactController extends Controller
{
    use HandleExceptions;

    public function __construct(protected WebhookContactEventService $contactEventService) {}

    public function update(UpdateContactRequest $request)
    {
        try {
            $contactEvent = $this->contactEventService->createContactEvent($request->validated());
            $contactEvent->refresh();
            return new ContactResource($contactEvent->contact);
        } 
        catch (Exception $exception) {
            return $this->handleErrorResponse($exception, "Unexpected Error");
        }
    }
}

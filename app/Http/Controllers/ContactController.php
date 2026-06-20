<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateContactRequest;
use App\Http\Resources\ContactResource;
use App\Services\ContactService;
use App\Traits\ErrorResponses;
use Exception;

class ContactController extends Controller
{
    use ErrorResponses;

    public function __construct(protected ContactService $contactService) {}

    public function store(CreateContactRequest $request)
    {
        try {
            $contact = $this->contactService->createContact($request->validated());
            $contact->refresh();
            return (new ContactResource($contact))->response()->setStatusCode(201);
        } catch (Exception $exception) {
            return $this->error($exception->getMessage(), $exception->getCode());
        }
    }
}

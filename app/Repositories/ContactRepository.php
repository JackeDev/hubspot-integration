<?php

namespace App\Repositories;

use App\Contracts\ContactRepositoryInterface;
use App\Models\Contact;
use Override;

class ContactRepository implements ContactRepositoryInterface
{
    #[Override]
    public function create(array $data): Contact
    {
        return Contact::create($data);
    }

    #[Override]
    public function update(Contact $contact, array $data): Contact
    {
        $contact->update($data);
        return $contact->fresh();
    }
}

<?php

namespace App\Repositories;

use App\Models\Contact;

class ContactRepository
{
    public function getById(int $id): Contact
    {
        return Contact::find($id);
    }

    public function create(array $data): Contact
    {
        return Contact::create($data);
    }

    public function update(Contact $contact, array $data): Contact
    {
        $contact->update($data);
        return $contact->fresh();
    }
}

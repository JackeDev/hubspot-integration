<?php

namespace App\Contracts;

use App\Models\Contact;

interface ContactRepositoryInterface
{
    public function create(array $data): Contact;

    public function update(Contact $contact, array $data): Contact;
}

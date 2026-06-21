<?php

namespace App\Repositories;

use App\Models\Contact;
use App\Models\WebhookContactEvent;

class WebhookContactEventRepository
{
    public function create(array $data): WebhookContactEvent
    {
        return WebhookContactEvent::create($data);
    }

    public function update(WebhookContactEvent $contactEvent, array $data): WebhookContactEvent
    {
        $contactEvent->update($data);
        return $contactEvent->fresh();
    }
}

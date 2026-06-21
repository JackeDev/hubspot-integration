<?php

namespace App\Services;

use App\Models\WebhookContactEvent;
use App\Repositories\ContactRepository;
use App\Repositories\WebhookContactEventRepository;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class WebhookContactEventService
{
    public function __construct(
        protected WebhookContactEventRepository $contactEventRepository,
        protected ContactRepository $contactRepository
    ) {}

    public function createContactEvent(array $data): WebhookContactEvent
    {
        $payload = json_encode($data);
        $properties = Arr::except($data, ['event_id', 'contact_id']);
        $data['payload'] = $payload;

        return DB::transaction(function() use($data, $properties) {
            $contact = $this->contactRepository->getFirstByProperty('client_id', $data['contact_id']);
            if ($contact) {
                $properties['last_client_updated'] = now();
                $this->contactRepository->update($contact, $properties);
                $contactEvent = $this->contactEventRepository->create([
                    'event_id'     => $data['event_id'],
                    'contact_id'   => $data['contact_id'],
                    'payload'      => $data['payload'],
                    'processed_at' => now(),
                ]);
                return $contactEvent;
            }
        });
    }
}

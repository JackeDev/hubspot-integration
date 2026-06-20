<?php

namespace App\Http\Controllers;

use Tambourine\HubspotClient\Services\HubspotContactService;

class TestController extends Controller
{
    public function __construct(protected HubspotContactService $client) {}

    public function test()
    {
        return $this->client->create([
            "firstname" => "John",
            'lastname'  => 'lastname',
            'email'     => 'email',
            'phone'     => 'phone' ?? null,
        ]);
    }
}

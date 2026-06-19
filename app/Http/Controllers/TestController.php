<?php

namespace App\Http\Controllers;

use Tambourine\HubspotClient\Contracts\HubspotServiceInterface;

class TestController extends Controller
{
    public function __construct(protected HubspotServiceInterface $client) {}

    public function test()
    {
        return $this->client->createContact([
            "firstname" => "John",
            'lastname'  => 'lastname',
            'email'     => 'email',
            'phone'     => 'phone' ?? null,
        ]);
    }
}

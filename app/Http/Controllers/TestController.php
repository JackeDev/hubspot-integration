<?php

namespace App\Http\Controllers;

use App\Services\Hubspot\Contracts\HubspotServiceInterface;

class TestController extends Controller
{
    public function __construct(protected HubspotServiceInterface $client){}

    public function test()
    {
        /*$service = new HubspotClient("https://api.hubapi.com/crm/v3/objects", "pat-na1-2844b2b0-8b98-4166-nee8-8fb4ef2ca7bb");
        return $service->createContact(["firstname" => "John"]);*/
        return $this->client->createContact(["firstname" => "John"]);
    }
}

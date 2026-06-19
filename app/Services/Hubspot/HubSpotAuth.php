<?php

namespace App\Services\Hubspot;

use Illuminate\Support\Facades\Log;

abstract class HubSpotAuth
{
    public function getToken(): string
    {
        return config('hubspot.token');
    }

    public function handleExpiredToken(): void
    {
        Log::channel('hubspot')->warning('HubSpot token expired or unauthorized. Manual token replacement required.');
    }
}

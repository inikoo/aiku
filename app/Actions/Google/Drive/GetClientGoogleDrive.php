<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Wed, 05 Jul 2023 13:43:20 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Google\Drive;

use App\Actions\Google\Drive\Traits\WithTokenPath;
use App\Models\Tenancy\Tenant;
use Google_Client;
use Google_Service_Drive;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;
use Lorisleiva\Actions\Concerns\AsAction;

class GetClientGoogleDrive
{
    use AsAction;
    use WithTokenPath;

    /**
     * @throws \Google\Exception
     */
    public function handle(): Google_Service_Drive
    {
        Tenant::where('slug', 'aroma')->first()->makeCurrent();
        $tokenPath = $this->getTokenPath();

        $client = $this->getClient($tokenPath);

        return new Google_Service_Drive($client);
    }

    /**
     * @throws \Google\Exception
     */
    public function getClient($tokenPath): RedirectResponse|Google_Client
    {
        $client = new Google_Client();
        $tenant = app('currentTenant');

        $client->setApplicationName('Aiku google drive manager');
        $client->setAuthConfig([
            'client_id'     => Arr::get($tenant->settings, 'google.id'),
            'client_secret' => Arr::get($tenant->settings, 'google.secret')
        ]);

        $client->setAccessType('offline');
        $client->setScopes(
            [
                Google_Service_Drive::DRIVE_METADATA,
                Google_Service_Drive::DRIVE_FILE,
                Google_Service_Drive::DRIVE
            ]
        );

        if (file_exists($tokenPath)) {
            $accessToken = json_decode(file_get_contents($tokenPath), true);
            $client->setAccessToken($accessToken);
        }

        return $client;
    }
}

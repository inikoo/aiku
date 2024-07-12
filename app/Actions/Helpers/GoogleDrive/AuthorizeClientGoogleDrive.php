<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 09 Jun 2024 13:33:20 Central European Summer Time, Plane Abu Dhabi - Kuala Lumpur
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Helpers\GoogleDrive;

use App\Actions\Helpers\GoogleDrive\Traits\WithTokenPath;
use App\Models\SysAdmin\Organisation;
use Exception;
use Google_Client;
use Google_Service_Drive;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;
use Lorisleiva\Actions\Concerns\AsAction;

class AuthorizeClientGoogleDrive
{
    use AsAction;
    use WithTokenPath;

    /**
     * @throws \Exception
     */
    public function handle(Organisation $organisation): RedirectResponse
    {
        $client       = new Google_Client();

        $tokenPath = $this->getTokenPath();

        $authCode = request()->query('code');
        $client->setRedirectUri('http://localhost:5173');
        $client->setApplicationName('Aiku google drive manager');
        $client->setAuthConfig([
            'client_id'     => Arr::get($organisation->settings, 'google.id'),
            'client_secret' => Arr::get($organisation->settings, 'google.secret')
        ]);

        $client->setAccessType('offline');
        $client->setScopes(
            [
                Google_Service_Drive::DRIVE_METADATA,
                Google_Service_Drive::DRIVE_FILE,
                Google_Service_Drive::DRIVE
            ]
        );

        if (blank($authCode)) {
            // If there is no previous token, or it's expired.
            if ($client->isAccessTokenExpired()) {
                // Refresh the token if possible, else fetch a new one.
                if ($client->getRefreshToken()) {
                    $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
                } else {
                    // Request authorization from the user.
                    $authUrl = $client->createAuthUrl();

                    return redirect()->away($authUrl);
                }
            }
        }

        // Exchange authorization code for an access token.
        $accessToken = $client->fetchAccessTokenWithAuthCode($authCode);
        $client->setAccessToken($accessToken);

        // Check to see if there was an error.
        if (array_key_exists('error', $accessToken)) {
            throw new Exception(join(', ', $accessToken));
        }

        // Save the token to a file.
        if (!file_exists(dirname($tokenPath))) {
            mkdir(dirname($tokenPath), 0700, true);
        }
        file_put_contents($tokenPath, json_encode($client->getAccessToken()));

        return redirect()->route('grp.sysadmin.settings.edit');
    }

    /**
     * @throws \Exception
     */
    public function asController(Organisation $organisation): RedirectResponse
    {
        return $this->handle($organisation);
    }
}

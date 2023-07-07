<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Wed, 05 Jul 2023 13:43:20 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Google\Drive;

use Google_Client;
use Google_Service_Drive;
use Lorisleiva\Actions\Concerns\AsAction;

class GetClientGoogleDrive
{
    use AsAction;

    /**
     * @throws \Google\Exception
     */
    public function handle(): Google_Service_Drive
    {
        $client = $this->getClient('resources/private/google/'.app('currentTenant')->slug.'-token.json');

        return new Google_Service_Drive($client);
    }

    /**
     * @throws \Google\Exception
     */
    public function getClient($tokenPath): Google_Client
    {
        $client = new Google_Client();
        $tenant = app('currentTenant');

        $client->setApplicationName('Aiku google drive manager');
        $client->setAuthConfig([
            'client_id' => json_decode($tenant->data, true)['google_cloud_client_id'],
            'client_secret' => json_decode($tenant->data, true)['google_cloud_client_secret'],
            'redirect_uris' => url('/'),
        ]);

        $client->setAccessType('offline');
        $client->setRedirectUri('http://localhost:5173');
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

        // If there is no previous token or it's expired.
        if ($client->isAccessTokenExpired()) {
            // Refresh the token if possible, else fetch a new one.
            if ($client->getRefreshToken()) {
                $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
            } else {
                // Request authorization from the user.
                $authUrl = $client->createAuthUrl();
                printf("Open the following link in your browser:\n%s\n", $authUrl);
                print 'Enter verification code: ';
                $authCode = trim(fgets(STDIN));

                // Exchange authorization code for an access token.
                $accessToken = $client->fetchAccessTokenWithAuthCode($authCode);
                $client->setAccessToken($accessToken);

                // Check to see if there was an error.
                if (array_key_exists('error', $accessToken)) {
                    throw new Exception(join(', ', $accessToken));
                }
            }
            // Save the token to a file.
            if (!file_exists(dirname($tokenPath))) {
                mkdir(dirname($tokenPath), 0700, true);
            }
            file_put_contents($tokenPath, json_encode($client->getAccessToken()));
        }

        return $client;
    }
}

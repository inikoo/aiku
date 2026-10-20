<?php

namespace App\Actions\Dispatching\Shipper;

use App\Actions\OrgAction;
use App\Models\Dispatching\Shipper;
use Illuminate\Console\Command;
use Laravel\Nightwatch\Facades\Nightwatch;

class SetShipperApiCredentials extends OrgAction
{
    public string $commandSignature = 'shipper:api_credentials {shipper : shipper slug} {api_shipper : e.g. dpd-gb}';

    public function handle(Shipper $shipper, string $apiShipper, string $accessToken): Shipper
    {
        $decodedToken = json_decode($accessToken, true);

        $settings = $shipper->settings;
        data_set($settings, 'access_token', is_array($decodedToken) ? $decodedToken : $accessToken);

        $shipper = UpdateShipper::make()->action($shipper, ['api_shipper' => $apiShipper]);
        $shipper->update(['settings' => $settings]);

        return $shipper;
    }

    public function asCommand(Command $command): int
    {
        Nightwatch::dontSample();

        $shipper     = Shipper::where('slug', $command->argument('shipper'))->firstOrFail();
        $accessToken = trim((string) $command->secret('Access token (paste the value, JSON for dpd-gb)'));

        if ($accessToken === '') {
            $command->error('No access token given, nothing changed');

            return 1;
        }

        $shipper = $this->handle($shipper, $command->argument('api_shipper'), $accessToken);

        $command->info("$shipper->slug ({$shipper->organisation->slug}) now uses $shipper->api_shipper with the new access token");

        return 0;
    }
}

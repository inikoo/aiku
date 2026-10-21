<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 21 Oct 2026 15:00:00 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Dispatching\Shipment\ApiCalls;

use App\Models\Dispatching\Shipment;
use Illuminate\Console\Command;
use Lorisleiva\Actions\Concerns\AsAction;
use RuntimeException;
use Sentry;
use Throwable;

/**
 * GLS Spain delivers in one to three days, so a parcel still undelivered after 30 days is lost or
 * returned and is no longer asked about. GLS stops answering ("Servicio no disponible") after a few
 * hundred lookups in quick succession, so they are spaced a second apart and a refusal ends the run;
 * the next hour carries on from the shipments still undelivered. Production only: a copy of the production database
 * elsewhere would otherwise mark real merchants' Shopify orders delivered.
 */
class TrackGlsEsShipments
{
    use AsAction;

    public string $commandSignature = 'shipments:track_gls_es';

    /**
     * @return array{checked: int, delivered: int}
     */
    public function handle(): array
    {
        $stats = ['checked' => 0, 'delivered' => 0];

        if (!app()->isProduction()) {
            return $stats;
        }

        Shipment::whereNull('delivered_at')
            ->where('created_at', '>', now()->subDays(30))
            ->whereHas('shipper', fn ($query) => $query->where('api_shipper', 'gls-es')->whereNotNull('settings->guid'))
            ->with('shipper')
            ->eachById(function (Shipment $shipment) use (&$stats) {
                try {
                    if (TrackGlsEsShipment::run($shipment)->delivered_at) {
                        $stats['delivered']++;
                    }
                    $stats['checked']++;
                } catch (RuntimeException $e) {
                    Sentry::captureMessage($e->getMessage().' after '.$stats['checked'].' shipments', Sentry\Severity::warning());

                    return false;
                } catch (Throwable $e) {
                    Sentry::captureException($e);
                }

                sleep(1);
            });

        return $stats;
    }

    public function asCommand(Command $command): int
    {
        $stats = $this->handle();
        $command->info("Checked {$stats['checked']} GLS Spain shipments, {$stats['delivered']} delivered");

        return 0;
    }
}

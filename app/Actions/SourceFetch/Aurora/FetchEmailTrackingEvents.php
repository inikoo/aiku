<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 12 Mar 2023 20:33:20 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\SourceFetch\Aurora;

use App\Actions\Mailroom\EmailTrackingEvent\StoreEmailTrackingEvent;
use App\Models\Mailroom\EmailTrackingEvent;
use App\Services\Tenant\SourceTenantService;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class FetchEmailTrackingEvents extends FetchAction
{
    public string $commandSignature = 'fetch:email-tracking-events {tenants?*} {--s|source_id=}';


    public function handle(SourceTenantService $tenantSource, int $tenantSourceId): ?EmailTrackingEvent
    {
        if ($emailTrackingEventData = $tenantSource->fetchEmailTrackingEvent($tenantSourceId)) {
            if (!$emailTrackingEventData['dispatchedEmail']) {
                return null;
            }

            if (!$emailTrackingEvent = EmailTrackingEvent::where('source_id', $emailTrackingEventData['emailTrackingEvent']['source_id'])
                ->first()) {
                $emailTrackingEvent = StoreEmailTrackingEvent::run(
                    dispatchedEmail: $emailTrackingEventData['dispatchedEmail'],
                    modelData: $emailTrackingEventData['emailTrackingEvent']
                );
            }

            DB::connection('aurora')->table('Email Tracking Event Dimension')
                ->where('Email Tracking Event Key', $emailTrackingEvent->source_id)
                ->update(['aiku_id' => $emailTrackingEvent->id]);

            return $emailTrackingEvent;
        }

        return null;
    }


    public function getModelsQuery(): Builder
    {
        return DB::connection('aurora')
            ->table('Email Tracking Event Dimension')
            ->select('Email Tracking Event Key as source_id')
            ->orderBy('source_id');
    }

    public function count(): ?int
    {
        return DB::connection('aurora')->table('Email Tracking Event Dimension')->count();
    }
}

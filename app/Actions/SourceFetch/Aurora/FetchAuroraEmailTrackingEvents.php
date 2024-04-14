<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 12 Mar 2023 20:33:20 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\SourceFetch\Aurora;

use App\Actions\Mail\EmailTrackingEvent\StoreEmailTrackingEvent;
use App\Models\Mail\EmailTrackingEvent;
use App\Services\Organisation\SourceOrganisationService;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class FetchAuroraEmailTrackingEvents extends FetchAuroraAction
{
    public string $commandSignature = 'fetch:email-tracking-events {organisations?*} {--s|source_id=}';


    public function handle(SourceOrganisationService $organisationSource, int $organisationSourceId): ?EmailTrackingEvent
    {
        if ($emailTrackingEventData = $organisationSource->fetchEmailTrackingEvent($organisationSourceId)) {
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

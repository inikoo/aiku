<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 26 Nov 2024 08:14:59 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Transfers\Aurora;

use App\Actions\Comms\EmailOngoingRun\StoreEmailOngoingRun;
use App\Actions\Comms\EmailOngoingRun\UpdateEmailOngoingRun;
use App\Models\Comms\EmailOngoingRun;
use App\Transfers\SourceOrganisationService;
use Exception;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Throwable;

class FetchAuroraEmailOngoingRuns extends FetchAuroraAction
{
    public string $commandSignature = 'fetch:email_ongoing_runs {organisations?*} {--s|source_id=} {--d|db_suffix=} {--N|only_new : Fetch only new}';


    public function handle(SourceOrganisationService $organisationSource, int $organisationSourceId): ?EmailOngoingRun
    {
        $emailRunData = $organisationSource->fetchEmailOngoingRun($organisationSourceId);
        if (!$emailRunData) {
            return null;
        }


        if ($emailRun = EmailOngoingRun::where('source_id', $emailRunData['email_ongoing_run']['source_id'])->first()) {
            try {
                $emailRun = UpdateEmailOngoingRun::make()->action(
                    emailRun: $emailRun,
                    modelData: $emailRunData['email_ongoing_run'],
                    hydratorsDelay: 60,
                    strict: false,
                );
                $this->recordChange($organisationSource, $emailRun->wasChanged());
            } catch (Exception $e) {
                $this->recordError($organisationSource, $e, $emailRunData['email_ongoing_run'], 'EmailOngoingRun', 'update');

                return null;
            }
        } else {
            try {
                $emailRun = StoreEmailOngoingRun::make()->action(
                    outbox: $emailRunData['outbox'],
                    modelData: $emailRunData['email_ongoing_run'],
                    hydratorsDelay: 60,
                    strict: false,
                );

                $this->recordNew($organisationSource);

                $sourceData = explode(':', $emailRun->source_id);
                DB::connection('aurora')->table('Email Campaign Dimension')
                    ->where('Email Campaign Key', $sourceData[1])
                    ->update(['alt_aiku_id' => $emailRun->id]);
            } catch (Exception|Throwable $e) {
                $this->recordError($organisationSource, $e, $emailRunData['emailRun'], 'EmailOngoingRun', 'store');

                return null;
            }
        }


        return $emailRun;
    }


    public function getModelsQuery(): Builder
    {
        //enum('Newsletter','Marketing','GR Reminder','AbandonedCart','Invite EmailOngoingRun','OOS Notification','Invite Full EmailOngoingRun')
        $query = DB::connection('aurora')
            ->table('Email Campaign Dimension')
            ->whereNotIn('Email Campaign Type', ['Newsletter', 'Marketing', 'Invite Full Mailshot', 'AbandonedCart']);

        if ($this->onlyNew) {
            $query->whereNull('alt_aiku_id');
        }

        return $query->select('Email Campaign Key as source_id')
            ->orderBy('source_id');
    }

    public function count(): ?int
    {
        $query = DB::connection('aurora')
            ->table('Email Campaign Dimension')
            ->whereNotIn('Email Campaign Type', ['Newsletter', 'Marketing', 'Invite Full Mailshot', 'AbandonedCart']);
        if ($this->onlyNew) {
            $query->whereNull('alt_aiku_id');
        }

        return $query->count();
    }
}

<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 24 Nov 2024 11:36:53 Central Indonesia Time, Kuta, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Transfers\Aurora;

use App\Actions\Comms\EmailBulkRun\StoreEmailBulkRun;
use App\Actions\Comms\EmailBulkRun\UpdateEmailBulkRun;
use App\Models\Comms\EmailBulkRun;
use App\Transfers\SourceOrganisationService;
use Exception;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Throwable;

class FetchAuroraEmailBulkRuns extends FetchAuroraAction
{
    public string $commandSignature = 'fetch:email_bulk_runs {organisations?*} {--s|source_id=} {--d|db_suffix=} {--N|only_new : Fetch only new}';


    public function handle(SourceOrganisationService $organisationSource, int $organisationSourceId): ?EmailBulkRun
    {
        $emailRunData = $organisationSource->fetchEmailBulkRun($organisationSourceId);
        if (!$emailRunData) {
            return null;
        }


        if ($emailRun = EmailBulkRun::where('source_id', $emailRunData['email_bulk_run']['source_id'])->first()) {
            try {
                $emailRun = UpdateEmailBulkRun::make()->action(
                    emailRun: $emailRun,
                    modelData: $emailRunData['email_bulk_run'],
                    hydratorsDelay: 60,
                    strict: false,
                );
                $this->recordChange($organisationSource, $emailRun->wasChanged());
            } catch (Exception $e) {
                $this->recordError($organisationSource, $e, $emailRunData['email_bulk_run'], 'EmailBulkRun', 'update');

                return null;
            }
        } else {
            try {
                $emailRun = StoreEmailBulkRun::make()->action(
                    outbox: $emailRunData['outbox'],
                    modelData: $emailRunData['email_bulk_run'],
                    hydratorsDelay: 60,
                    strict: false,
                );

                $this->recordNew($organisationSource);

                $sourceData = explode(':', $emailRun->source_id);
                DB::connection('aurora')->table('Email Campaign Dimension')
                    ->where('Email Campaign Key', $sourceData[1])
                    ->update(['alt_aiku_id' => $emailRun->id]);
            } catch (Exception|Throwable $e) {
                $this->recordError($organisationSource, $e, $emailRunData['emailRun'], 'EmailBulkRun', 'store');

                return null;
            }
        }


        return $emailRun;
    }


    public function getModelsQuery(): Builder
    {
        //enum('Newsletter','Marketing','GR Reminder','AbandonedCart','Invite Mailshot','OOS Notification','Invite Full Mailshot')
        $query = DB::connection('aurora')
            ->table('Email Campaign Dimension')
            ->whereIn('Email Campaign Type', ['GR Reminder', 'OOS Notification']);

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
            ->whereIn('Email Campaign Type', ['GR Reminder', 'OOS Notification']);
        if ($this->onlyNew) {
            $query->whereNull('alt_aiku_id');
        }

        return $query->count();
    }
}

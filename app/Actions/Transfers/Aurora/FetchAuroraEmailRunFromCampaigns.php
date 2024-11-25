<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 24 Nov 2024 11:36:53 Central Indonesia Time, Kuta, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Transfers\Aurora;

use App\Actions\Comms\EmailRun\StoreEmailRun;
use App\Actions\Comms\EmailRun\UpdateEmailRun;
use App\Models\Comms\EmailRun;
use App\Transfers\SourceOrganisationService;
use Exception;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class FetchAuroraEmailRunFromCampaigns extends FetchAuroraAction
{
    public string $commandSignature = 'fetch:email_runs_from_campaigns {organisations?*} {--s|source_id=} {--d|db_suffix=} {--N|only_new : Fetch only new}';


    public function handle(SourceOrganisationService $organisationSource, int $organisationSourceId): ?EmailRun
    {
        $emailRunData = $organisationSource->fetchEmailRunFromCampaign($organisationSourceId);
        if (!$emailRunData) {
            return null;
        }


        if ($emailRun = EmailRun::where('source_id', $emailRunData['email_run']['source_id'])->first()) {
            //  try {
            $emailRun = UpdateEmailRun::make()->action(
                emailRun: $emailRun,
                modelData: $emailRunData['email_run'],
                hydratorsDelay: 60,
                strict: false,
            );
            $this->recordChange($organisationSource, $emailRun->wasChanged());
            //            } catch (Exception $e) {
            //                $this->recordError($organisationSource, $e, $emailRunData['email_run'], 'EmailRun', 'update');
            //
            //                return null;
            //            }
        } else {
            // try {
            $emailRun = StoreEmailRun::make()->action(
                outbox: $emailRunData['outbox'],
                modelData: $emailRunData['email_run'],
                hydratorsDelay: 60,
                strict: false,
            );

            $this->recordNew($organisationSource);

            $sourceData = explode(':', $emailRun->source_id);
            DB::connection('aurora')->table('Email Campaign Dimension')
                ->where('Email Campaign Key', $sourceData[1])
                ->update(['alt_aiku_id' => $emailRun->id]);
            //                } catch (Exception|Throwable $e) {
            //
            //                    $this->recordError($organisationSource, $e, $emailRunData['emailRun'], 'EmailRun', 'store');
            //
            //                    return null;
            //                }
        }


        return $emailRun;
    }


    public function getModelsQuery(): Builder
    {
        //enum('Newsletter','Marketing','GR Reminder','AbandonedCart','Invite EmailRun','OOS Notification','Invite Full EmailRun')
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

<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 10 Mar 2023 20:59:01 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Transfers\Aurora;

use App\Actions\Mail\DispatchedEmail\StoreDispatchEmail;
use App\Actions\Mail\DispatchedEmail\UpdateDispatchedEmail;
use App\Models\Mail\DispatchedEmail;
use App\Transfers\SourceOrganisationService;
use Exception;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Throwable;

class FetchAuroraDispatchedEmails extends FetchAuroraAction
{
    public string $commandSignature = 'fetch:dispatched_emails {organisations?*} {--s|source_id=} {--d|db_suffix=}';


    public function handle(SourceOrganisationService $organisationSource, int $organisationSourceId): ?DispatchedEmail
    {
        $dispatchedEmail = null;
        if ($dispatchedEmailData = $organisationSource->fetchDispatchedEmail($organisationSourceId)) {
            if ($dispatchedEmail = DispatchedEmail::where('source_id', $dispatchedEmailData['dispatchedEmail']['source_id'])
                ->first()) {
                //try {
                $dispatchedEmail = UpdateDispatchedEmail::make()->action(
                    dispatchedEmail: $dispatchedEmail,
                    modelData: $dispatchedEmailData['dispatchedEmail'],
                    hydratorsDelay: 60,
                    strict: false,
                );
                $this->recordChange($organisationSource, $dispatchedEmail->wasChanged());
                //                } catch (Exception $e) {
                //                    $this->recordError($organisationSource, $e, $dispatchedEmailData['dispatchedEmail'], 'DispatchedEmail', 'update');
                //
                //                    return null;
                //                }
            } else {
                // try {
                $dispatchedEmail = StoreDispatchEmail::make()->action(
                    parent: $dispatchedEmailData['parent'],
                    recipient: $dispatchedEmailData['recipient'],
                    modelData: $dispatchedEmailData['dispatchedEmail'],
                    hydratorsDelay: 60,
                    strict: false,
                );

                $this->recordNew($organisationSource);
                $sourceData = explode(':', $dispatchedEmail->source_id);
                DB::connection('aurora')->table('Email Tracking Dimension')
                    ->where('Email Tracking Key', $sourceData[1])
                    ->update(['aiku_id' => $dispatchedEmail->id]);
                //                } catch (Exception|Throwable $e) {
                //                    $this->recordError($organisationSource, $e, $dispatchedEmailData['dispatchedEmail'], 'DispatchedEmail', 'store');
                //
                //                    return null;
                //                }
            }
        }

        return $dispatchedEmail;
    }


    public function getModelsQuery(): Builder
    {
        return DB::connection('aurora')
            ->table('Email Tracking Dimension')
            ->select('Email Tracking Key as source_id')
            ->orderBy('Email Tracking Created Date');
    }

    public function count(): ?int
    {
        return DB::connection('aurora')->table('Email Tracking Dimension')->count();
    }
}

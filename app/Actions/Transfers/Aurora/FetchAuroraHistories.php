<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 04 Oct 2024 11:58:26 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Transfers\Aurora;

use App\Actions\Helpers\History\StoreHistory;
use App\Actions\Helpers\History\UpdateHistory;
use App\Models\Helpers\History;
use App\Transfers\SourceOrganisationService;
use Exception;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Throwable;

class FetchAuroraHistories extends FetchAuroraAction
{
    public string $commandSignature = 'fetch:histories {organisations?*} {--s|source_id=} {--N|only_new : Fetch only new}  {--d|db_suffix=} {--r|reset}';


    public function handle(SourceOrganisationService $organisationSource, int $organisationSourceId): ?History
    {
        if ($historyData = $organisationSource->fetchHistory($organisationSourceId)) {
            if ($history = History::where('source_id', $historyData['history']['source_id'])->first()) {
                //   try {
                $history = UpdateHistory::make()->action(
                    history: $history,
                    modelData: $historyData['history'],
                    hydratorsDelay: 60,
                );
                $this->recordChange($organisationSource, $history->wasChanged());
                //                } catch (Exception $e) {
                //                    $this->recordError($organisationSource, $e, $historyData['history'], 'History', 'update');
                //
                //                    return null;
                //                }
            } else {
                //     try {
                $history = StoreHistory::make()->action(
                    auditable: $historyData['auditable'],
                    modelData: $historyData['history'],
                    hydratorsDelay: 60,
                );

                $sourceData = explode(':', $history->source_id);
                DB::connection('aurora')->table('History Dimension')
                    ->where('History Key', $sourceData[1])
                    ->update(['aiku_id' => $history->id]);


                //                } catch (Exception|Throwable $e) {
                //                    $this->recordError($organisationSource, $e, $historyData['history'], 'History', 'store');
                //                    return null;
                //                }
            }


            // dd($history);
            return $history;
        }

        return null;
    }

    public function getModelsQuery(): Builder
    {
        //enum('sold_since','last_sold','first_sold','placed','wrote','deleted','edited','cancelled','charged','merged','created','associated','disassociate','register','login','logout','fail_login','password_request','password_reset','search')
        $query = DB::connection('aurora')
            ->table('History Dimension')
            ->where('Direct Object', 'Customer')
            //    ->whereIn('Action', ['edited', 'created']);
            ->whereIn('Action', ['created'])
            ->select('History Key as source_id')
            ->orderBy('History Date', 'asc');

        if ($this->onlyNew) {
            $query->whereNull('aiku_id');
        }

        return $query;
    }

    public function count(): ?int
    {
        $query = DB::connection('aurora')
            ->table('History Dimension')
            ->where('Direct Object', 'Customer')
            //    ->whereIn('Action', ['edited', 'created']);
            ->whereIn('Action', ['created']);
        if ($this->onlyNew) {
            $query->whereNull('aiku_id');
        }

        return $query->count();
    }


}

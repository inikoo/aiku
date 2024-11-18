<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 18 Nov 2024 15:25:27 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Transfers\Aurora;

use App\Actions\Ordering\Purge\StorePurge;
use App\Actions\Ordering\Purge\UpdatePurge;
use App\Models\Ordering\Purge;
use App\Transfers\SourceOrganisationService;
use Exception;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Throwable;

class FetchAuroraPurges extends FetchAuroraAction
{
    public string $commandSignature = 'fetch:purges {organisations?*} {--s|source_id=} {--d|db_suffix=}';

    public function handle(SourceOrganisationService $organisationSource, int $organisationSourceId): ?Purge
    {
        if ($purgeData = $organisationSource->fetchPurge($organisationSourceId)) {
            if ($purge = Purge::where('source_id', $purgeData['purge']['source_id'])->first()) {
                //try {
                $purge = UpdatePurge::make()->action(
                    purge: $purge,
                    modelData: $purgeData['purge'],
                    hydratorsDelay: 60,
                    strict: false,
                    audit: false
                );
                $this->recordChange($organisationSource, $purge->wasChanged());
                //                } catch (Exception $e) {
                //                    $this->recordError($organisationSource, $e, $purgeData['purge'], 'Purge', 'update');
                //                    return null;
                //                }
            } else {
                // try {

                $purge = StorePurge::make()->action(
                    shop: $purgeData['shop'],
                    modelData: $purgeData['purge'],
                    hydratorsDelay: 60,
                    strict: false,
                    audit: false
                );

                Purge::enableAuditing();
                $this->saveMigrationHistory(
                    $purge,
                    Arr::except($purgeData['purge'], ['fetched_at', 'last_fetched_at', 'source_id'])
                );

                $this->recordNew($organisationSource);

                $sourceData = explode(':', $purge->source_id);
                DB::connection('aurora')->table('Order Basket Purge Dimension')
                    ->where('Order Basket Purge Key', $sourceData[1])
                    ->update(['aiku_id' => $purge->id]);
                //                } catch (Exception|Throwable $e) {
                //                    $this->recordError($organisationSource, $e, $purgeData['purge'], 'Purge', 'store');
                //                    return null;
                //                }
            }


            return $purge;
        }

        return null;
    }

    public function getModelsQuery(): Builder
    {
        return DB::connection('aurora')
            ->table('Order Basket Purge Dimension')
            ->select('Order Basket Purge Key as source_id')
            ->orderBy('source_id');


    }


    public function count(): ?int
    {
        return DB::connection('aurora')->table('Order Basket Purge Dimension')->count();
    }
}

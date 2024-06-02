<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 09 Apr 2024 12:35:47 Central Indonesia Time, Bali Office , Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\SourceFetch\Aurora;

use App\Actions\Catalogue\Product\StoreOuterTODELETE;
use App\Actions\Catalogue\Product\ProductOuter;
use App\Models\Catalogue\Product;
use App\Services\Organisation\SourceOrganisationService;
use Exception;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class FetchAuroraOuters extends FetchAuroraAction
{
    public string $commandSignature = 'fetch:outers {organisations?*} {--s|source_id=} {--S|shop= : Shop slug} {--N|only_new : Fetch only new}  {--d|db_suffix=} {--r|reset}';

    public function handle(SourceOrganisationService $organisationSource, int $organisationSourceId): ?Product
    {
        if ($outerData = $organisationSource->fetchOuter($organisationSourceId)) {


            if ($outer = Product::withTrashed()->where('source_id', $outerData['outer']['source_id'])
                ->first()) {
                try {
                    $outer = ProductOuter::make()->action(
                        outer: $outer,
                        modelData: $outerData['outer'],
                    );
                } catch (Exception $e) {
                    $this->recordError($organisationSource, $e, $outerData['outer'], 'Product', 'update');
                    return null;
                }
            } else {
                try {
                    $outer = StoreOuterTODELETE::make()->action(
                        product: $outerData['product'],
                        modelData: $outerData['outer'],
                    );
                } catch (Exception $e) {
                    $this->recordError($organisationSource, $e, $outerData['outer'], 'Product', 'store');
                    return null;
                }
            }


            $sourceData = explode(':', $outer->source_id);

            DB::connection('aurora')->table('Product Dimension')
                ->where('Product ID', $sourceData[1])
                ->update(['aiku_id' => $outer->id]);

            return $outer;
        }


        return null;
    }

    public function getModelsQuery(): Builder
    {
        $query = DB::connection('aurora')
            ->table('Product Dimension')
            ->where('Product Type', 'Product')
            ->where('is_variant', 'Yes')
            ->select('Product ID as source_id')
            ->orderBy('Product ID');

        if ($this->onlyNew) {
            $query->whereNull('aiku_id');
        }

        if ($this->shop) {
            $sourceData = explode(':', $this->shop->source_id);
            $query->where('Product Store Key', $sourceData[1]);
        }

        return $query;
    }

    public function count(): ?int
    {
        $query = DB::connection('aurora')->table('Product Dimension')
            ->where('is_variant', 'Yes')
            ->where('Product Type', 'Product');

        if ($this->onlyNew) {
            $query->whereNull('aiku_id');
        }

        if ($this->shop) {
            $sourceData = explode(':', $this->shop->source_id);
            $query->where('Product Store Key', $sourceData[1]);
        }

        return $query->count();
    }

    public function reset(): void
    {
        DB::connection('aurora')->table('Product Dimension')->update(['aiku_id' => null]);
    }
}

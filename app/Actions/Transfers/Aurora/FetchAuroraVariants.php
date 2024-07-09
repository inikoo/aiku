<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 09 Apr 2024 12:35:47 Central Indonesia Time, Bali Office , Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Transfers\Aurora;

use App\Actions\Catalogue\ProductVariant\StoreProductVariant;
use App\Actions\Catalogue\ProductVariant\UpdateProductVariant;
use App\Models\Catalogue\ProductVariant;
use App\Transfers\SourceOrganisationService;
use Exception;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class FetchAuroraVariants extends FetchAuroraAction
{
    public string $commandSignature = 'fetch:variants {organisations?*} {--s|source_id=} {--S|shop= : Shop slug} {--N|only_new : Fetch only new}  {--d|db_suffix=} {--r|reset}';

    public function handle(SourceOrganisationService $organisationSource, int $organisationSourceId): ?ProductVariant
    {


        if ($productVariantData = $organisationSource->fetchVariant($organisationSourceId)) {


            if ($productVariant = ProductVariant::withTrashed()->where('source_id', $productVariantData['variant']['source_id'])
                ->first()) {

                try {
                    $productVariant = UpdateProductVariant::make()->action(
                        productVariant: $productVariant,
                        modelData: $productVariantData['variant'],
                    );
                } catch (Exception $e) {
                    $this->recordError($organisationSource, $e, $productVariantData['variant'], 'Product', 'update');
                    return null;
                }
            } else {

                try {
                    $productVariant = StoreProductVariant::make()->action(
                        product: $productVariantData['product'],
                        modelData: $productVariantData['variant'],
                        hydratorsDelay: 120
                    );
                } catch (Exception $e) {
                    $this->recordError($organisationSource, $e, $productVariantData['variant'], 'Product', 'store');
                    return null;
                }
            }


            $sourceData = explode(':', $productVariant->source_id);

            DB::connection('aurora')->table('Product Dimension')
                ->where('Product ID', $sourceData[1])
                ->update(['aiku_id' => $productVariant->id]);

            return $productVariant;
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

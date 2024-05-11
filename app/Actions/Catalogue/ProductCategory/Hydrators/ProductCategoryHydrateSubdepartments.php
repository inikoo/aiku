<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Fri, 10 Mar 2023 11:05:41 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Catalogue\ProductCategory\Hydrators;

use App\Models\Catalogue\ProductCategory;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Lorisleiva\Actions\Concerns\AsAction;

class ProductCategoryHydrateSubdepartments implements ShouldBeUnique
{
    use AsAction;


    public function handle(ProductCategory $productCategory): void
    {
        /*
        $stats         = [
            'number_families' => $productCategory->families->count(),
        ];
        $stateCounts   = Family::where('productCategory_id', $productCategory->id)
            ->selectRaw('state, count(*) as total')
            ->groupBy('state')
            ->pluck('total', 'state')->all();

        foreach (FamilyStateEnum::cases() as $familyState) {
            $stats['number_families_state_'.$familyState->snake()] = Arr::get($stateCounts, $familyState->value, 0);
        }
        $productCategory->stats()->update($stats);
        */
    }

    public function getJobUniqueId(ProductCategory $productCategory): int
    {
        return $productCategory->id;
    }
}

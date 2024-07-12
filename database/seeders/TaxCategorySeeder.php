<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 11 Jul 2024 17:12:30 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace Database\Seeders;

use App\Enums\Helpers\TaxCategories\TaxCategoryTypeEnum;
use App\Models\Helpers\Country;
use App\Models\Helpers\TaxCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class TaxCategorySeeder extends Seeder
{
    public function run(): void
    {
        $taxCategories = json_decode(Storage::disk('datasets')->get('tax-categories.json'));




        foreach ($taxCategories as $taxCategory) {

            $type=match ($taxCategory->{'Tax Category Type'}) {
                'Standard' => TaxCategoryTypeEnum::STANDARD,
                'Reduced'  => TaxCategoryTypeEnum::REDUCED,
                'Outside'  => TaxCategoryTypeEnum::OUTSIDE,
                'EU_VTC'   => TaxCategoryTypeEnum::EU_VTC,
                'Standard_Re','Standard+RE' => TaxCategoryTypeEnum::STANDARD_RE,
                'Reduced+RE'=> TaxCategoryTypeEnum::REDUCED_RE,
                default     => TaxCategoryTypeEnum::LEGACY,
            };

            TaxCategory::upsert(
                [
                    [
                        'code'       => $taxCategory->{'Tax Category Code'},
                        'name'       => $taxCategory->{'Tax Category Name'},
                        'status'     => $taxCategory->{'Tax Category Active'} === 'Yes',
                        'rate'       => $taxCategory->{'Tax Category Rate'},
                        'country_id' => Country::where('code', $taxCategory->{'Tax Category Country 2 Alpha Code'})->value('id'),
                        'data'       => $taxCategory->{'Tax Category Metadata'},
                        'type'       => $type,
                        'type_name'  => $taxCategory->{'Tax Category Type Name'},
                        'source_id'  => $taxCategory->{'Tax Category Key'},
                    ],
                ],
                ['code'],
                ['name', 'status']
            );
        }
    }
}

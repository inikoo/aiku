<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 15 Oct 2026 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\SupplyChain\SupplierProduct;

use App\Enums\SupplyChain\SupplierProduct\PackagingTermPayerEnum;
use Illuminate\Validation\Rule;

trait WithPackagingTermsRules
{
    private const PAID_PACKAGING_TERMS = ['retail_box', 'extra_wrapping', 'special_packaging', 'label_application'];

    /**
     * @return array<string, array<int, mixed>>
     */
    protected function packagingTermsRules(): array
    {
        $rules = [
            'packaging_terms'                      => ['sometimes', 'array:'.implode(',', [...self::PAID_PACKAGING_TERMS, 'protective_materials', 'silica_gel_sachets'])],
            'packaging_terms.protective_materials' => ['sometimes', 'nullable', 'string', 'max:255'],
            'packaging_terms.silica_gel_sachets'   => ['sometimes', 'nullable', 'integer', 'min:0'],
        ];

        foreach (self::PAID_PACKAGING_TERMS as $term) {
            $rules["packaging_terms.$term"] = ['sometimes', 'nullable', Rule::enum(PackagingTermPayerEnum::class)];
        }

        return $rules;
    }
}

<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 13 Oct 2024 16:41:00 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Traits\Rules;

trait WithOrderingAmountNoStrictFields
{
    protected function mergeOrderingAmountNoStrictFields(array $rules): array
    {
        $rules['grp_exchange'] = ['sometimes', 'numeric'];
        $rules['org_exchange'] = ['sometimes', 'numeric'];

        $rules['gross_amount']    = ['sometimes', 'numeric'];
        $rules['goods_amount']    = ['sometimes', 'numeric'];
        $rules['services_amount'] = ['sometimes', 'numeric'];

        $rules['shipping_amount']  = ['sometimes', 'numeric'];
        $rules['charges_amount']   = ['sometimes', 'numeric'];
        $rules['insurance_amount'] = ['sometimes', 'numeric'];

        $rules['net_amount']   = ['sometimes', 'numeric'];
        $rules['tax_amount']   = ['sometimes', 'numeric'];
        $rules['total_amount'] = ['sometimes', 'numeric'];
        return $rules;
    }
}

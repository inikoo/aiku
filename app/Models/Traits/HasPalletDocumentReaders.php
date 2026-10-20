<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 17 Oct 2026 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Models\Traits;

use App\Models\SysAdmin\User;

trait HasPalletDocumentReaders
{
    /**
     * The PDF and picking lists of a pallet delivery or return are read by the staff of its
     * fulfilment shop or by the fulfilment staff of the warehouse the pallets are in.
     */
    public function documentsCanBeReadBy(User $user): bool
    {
        return $user->authTo([
            "fulfilment-shop.$this->fulfilment_id.view",
            "fulfilment-shop.$this->fulfilment_id.edit",
            "supervisor-fulfilment-shop.$this->fulfilment_id",
            "fulfilment.$this->warehouse_id.view",
            "fulfilment.$this->warehouse_id.edit",
            "supervisor-fulfilment.$this->warehouse_id",
        ]);
    }
}

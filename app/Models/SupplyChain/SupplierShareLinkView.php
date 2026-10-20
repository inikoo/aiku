<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 19 Oct 2026 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Models\SupplyChain;

use Illuminate\Database\Eloquent\Model;

class SupplierShareLinkView extends Model
{
    public const UPDATED_AT = null;

    protected $casts = [
        'created_at' => 'datetime',
    ];

    protected $guarded = [];
}

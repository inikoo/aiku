<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 26 Jul 2024 13:54:09 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Models\Fulfilment;

use Illuminate\Database\Eloquent\Model;

/**
 *
 *
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|StoredItemAuditDelta newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|StoredItemAuditDelta newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|StoredItemAuditDelta query()
 * @mixin \Eloquent
 */
class StoredItemAuditDelta extends Model
{
    protected $guarded = [];
}

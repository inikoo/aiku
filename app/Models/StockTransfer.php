<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 31 May 2024 16:02:10 Central European Summer Time, Mijas Costa, Spain
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 *
 *
 * @property int $id
 * @property int $group_id
 * @property int $organisation_id
 * @property string $slug
 * @property int $org_partner_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $deleted_at
 * @property string|null $source_id
 * @method static \Illuminate\Database\Eloquent\Builder|StockTransfer newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|StockTransfer newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|StockTransfer query()
 * @mixin \Eloquent
 */
class StockTransfer extends Model
{
}

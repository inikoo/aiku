<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 05 Jul 2023 15:31:04 Malaysia Time, Pantai Lembeng, Bali, Id
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Models\Web;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\Web\WebBlockStats
 *
 * @property int $id
 * @property int $web_block_id
 * @property int $number_tenants
 * @property int $number_websites
 * @property int $number_webpages
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Web\WebBlock $webBlock
 * @method static \Illuminate\Database\Eloquent\Builder|WebBlockStats newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|WebBlockStats newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|WebBlockStats query()
 * @mixin \Eloquent
 */
class WebBlockStats extends Model
{
    protected $table = 'web_block_stats';

    protected $guarded = [];

    public function webBlock(): BelongsTo
    {
        return $this->belongsTo(WebBlock::class);
    }
}

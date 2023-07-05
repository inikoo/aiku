<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 05 Jul 2023 15:04:23 Malaysia Time, Pantai Lembeng, Bali, Id
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Models\Web;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Multitenancy\Models\Concerns\UsesLandlordConnection;

/**
 * App\Models\Web\WebBlockTypeStats
 *
 * @property-read \App\Models\Web\WebBlockType|null $webBlockType
 * @method static \Illuminate\Database\Eloquent\Builder|WebBlockTypeStats newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|WebBlockTypeStats newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|WebBlockTypeStats query()
 * @mixin \Eloquent
 */
class WebBlockTypeStats extends Model
{
    use UsesLandlordConnection;

    protected $table = 'web_block_type_stats';

    protected $guarded = [];

    public function webBlockType(): BelongsTo
    {
        return $this->belongsTo(WebBlockType::class);
    }

}

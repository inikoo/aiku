<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 05 Jul 2023 15:04:23 Malaysia Time, Pantai Lembeng, Bali, Id
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Models\Web;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 *
 *
 * @property int $id
 * @property int $web_block_type_category_id
 * @property int $number_organisations
 * @property int $number_web_blocks
 * @property int $number_websites
 * @property int $number_webpages
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Web\WebBlockTypeCategory $webBlockTypeCategory
 * @method static Builder|WebBlockTypeCategoryStats newModelQuery()
 * @method static Builder|WebBlockTypeCategoryStats newQuery()
 * @method static Builder|WebBlockTypeCategoryStats query()
 * @mixin \Eloquent
 */
class WebBlockTypeCategoryStats extends Model
{
    protected $table = 'web_block_type_category_stats';

    protected $guarded = [];

    public function webBlockTypeCategory(): BelongsTo
    {
        return $this->belongsTo(WebBlockTypeCategory::class);
    }

}

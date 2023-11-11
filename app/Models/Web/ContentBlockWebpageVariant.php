<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 13 Jul 2023 14:24:05 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Models\Web;

use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * App\Models\Web\ContentBlockWebpageVariant
 *
 * @property int $id
 * @property int $webpage_variant_id
 * @property int $content_block_id
 * @property int $position
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|ContentBlockWebpageVariant newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ContentBlockWebpageVariant newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ContentBlockWebpageVariant query()
 * @mixin \Eloquent
 */
class ContentBlockWebpageVariant extends Pivot
{
    public $incrementing = true;

    protected $guarded = [];
}

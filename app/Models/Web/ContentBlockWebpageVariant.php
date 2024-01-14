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

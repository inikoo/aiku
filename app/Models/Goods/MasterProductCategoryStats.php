<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 25 Dec 2024 02:15:29 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Models\Goods;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Catalogue\MasterProductCategoryStats
 *
 * @property int $id
 * @property int $master_product_category_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static Builder<static>|MasterProductCategoryStats newModelQuery()
 * @method static Builder<static>|MasterProductCategoryStats newQuery()
 * @method static Builder<static>|MasterProductCategoryStats query()
 * @mixin Eloquent
 */
class MasterProductCategoryStats extends Model
{
    protected $table = 'master_product_category_stats';

    protected $guarded = [];


}

<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 26 Jun 2024 13:46:17 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Models\Ordering;

use App\Models\Traits\InShop;
use Illuminate\Database\Eloquent\Model;

/**
 *
 *
 * @property int $id
 * @property int $group_id
 * @property int|null $organisation_id
 * @property int $shop_id
 * @property int $website_id
 * @property int|null $webpage_id
 * @property int|null $position
 * @property int $web_block_id
 * @property string $model_type
 * @property int $model_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\SysAdmin\Group $group
 * @property-read \App\Models\SysAdmin\Organisation|null $organisation
 * @property-read \App\Models\Catalogue\Shop $shop
 * @method static \Illuminate\Database\Eloquent\Builder|ModelHasPlatform newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ModelHasPlatform newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ModelHasPlatform query()
 * @mixin \Eloquent
 */
class ModelHasPlatform extends Model
{
    use InShop;

    protected $table = 'model_has_web_blocks';

    protected $guarded = [];

    protected $casts = [
        'data' => 'array'
    ];



}

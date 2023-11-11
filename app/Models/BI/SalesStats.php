<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 20 Jun 2023 20:29:03 Malaysia Time, Pantai Lembeng, Bali, Id
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Models\BI;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Carbon;

/**
 * App\Models\BI\SalesStats
 *
 * @property int $id
 * @property string $model_type
 * @property int $model_id
 * @property string $scope
 * @property string $all
 * @property string $1y
 * @property string $1q
 * @property string $1m
 * @property string $1w
 * @property string $ytd
 * @property string $qtd
 * @property string $mtd
 * @property string $wtd
 * @property string $lm
 * @property string $lw
 * @property string $yda
 * @property string $tdy
 * @property string $all_ly
 * @property string $1y_ly
 * @property string $1q_ly
 * @property string $1m_ly
 * @property string $1w_ly
 * @property string $ytd_ly
 * @property string $qtd_ly
 * @property string $mtd_ly
 * @property string $wtd_ly
 * @property string $lm_ly
 * @property string $lw_ly
 * @property string $yda_ly
 * @property string $tdy_ly
 * @property string $py1
 * @property string $py2
 * @property string $py3
 * @property string $py4
 * @property string $py5
 * @property string $pq1
 * @property string $pq2
 * @property string $pq3
 * @property string $pq4
 * @property string $pq5
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Model|\Eloquent $model
 * @method static Builder|SalesStats newModelQuery()
 * @method static Builder|SalesStats newQuery()
 * @method static Builder|SalesStats query()
 * @mixin Eloquent
 */
class SalesStats extends Model
{
    protected $table   = 'sales_stats';
    protected $guarded = [];


    public function model(): MorphTo
    {
        return $this->morphTo('model');
    }
}

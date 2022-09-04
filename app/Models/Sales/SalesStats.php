<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Fri, 02 Sept 2022 14:48:52 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Models\Sales;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;



/**
 * App\Models\Sales\SalesStats
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
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read Model|\Eloquent $model
 * @method static Builder|SalesStats newModelQuery()
 * @method static Builder|SalesStats newQuery()
 * @method static Builder|SalesStats query()
 * @method static Builder|SalesStats where1m($value)
 * @method static Builder|SalesStats where1mLy($value)
 * @method static Builder|SalesStats where1q($value)
 * @method static Builder|SalesStats where1qLy($value)
 * @method static Builder|SalesStats where1w($value)
 * @method static Builder|SalesStats where1wLy($value)
 * @method static Builder|SalesStats where1y($value)
 * @method static Builder|SalesStats where1yLy($value)
 * @method static Builder|SalesStats whereAll($value)
 * @method static Builder|SalesStats whereCreatedAt($value)
 * @method static Builder|SalesStats whereId($value)
 * @method static Builder|SalesStats whereLm($value)
 * @method static Builder|SalesStats whereLmLy($value)
 * @method static Builder|SalesStats whereLw($value)
 * @method static Builder|SalesStats whereLwLy($value)
 * @method static Builder|SalesStats whereModelId($value)
 * @method static Builder|SalesStats whereModelType($value)
 * @method static Builder|SalesStats whereMtd($value)
 * @method static Builder|SalesStats whereMtdLy($value)
 * @method static Builder|SalesStats wherePq1($value)
 * @method static Builder|SalesStats wherePq2($value)
 * @method static Builder|SalesStats wherePq3($value)
 * @method static Builder|SalesStats wherePq4($value)
 * @method static Builder|SalesStats wherePq5($value)
 * @method static Builder|SalesStats wherePy1($value)
 * @method static Builder|SalesStats wherePy2($value)
 * @method static Builder|SalesStats wherePy3($value)
 * @method static Builder|SalesStats wherePy4($value)
 * @method static Builder|SalesStats wherePy5($value)
 * @method static Builder|SalesStats whereQtd($value)
 * @method static Builder|SalesStats whereQtdLy($value)
 * @method static Builder|SalesStats whereScope($value)
 * @method static Builder|SalesStats whereTdy($value)
 * @method static Builder|SalesStats whereTdyLy($value)
 * @method static Builder|SalesStats whereUpdatedAt($value)
 * @method static Builder|SalesStats whereWtd($value)
 * @method static Builder|SalesStats whereWtdLy($value)
 * @method static Builder|SalesStats whereYda($value)
 * @method static Builder|SalesStats whereYdaLy($value)
 * @method static Builder|SalesStats whereYtd($value)
 * @method static Builder|SalesStats whereYtdLy($value)
 * @mixin \Eloquent
 */
class SalesStats extends Model
{
    protected $table = 'sales_stats';
    protected $guarded = [];


    public function model(): MorphTo
    {
        return $this->morphTo('model');
    }
}

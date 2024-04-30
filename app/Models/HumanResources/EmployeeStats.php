<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 29 Apr 2024 16:23:02 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Models\HumanResources;

use Illuminate\Database\Eloquent\Model;

/**
 *
 *
 * @property int $id
 * @property int|null $employee_id
 * @property int $number_timesheets
 * @property int $number_time_trackers
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeStats newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeStats newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeStats query()
 * @mixin \Eloquent
 */
class EmployeeStats extends Model
{
    protected $guarded = [];

}

<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Mon, 12 Sept 2022 18:06:24 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Models\HumanResources;

use App\Actions\HumanResources\Employee\HydrateEmployee;
use App\Actions\HumanResources\JobPosition\HydrateJobPosition;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;


/**
 * App\Models\HumanResources\EmployeeJobPosition
 *
 * @property int $id
 * @property int $job_position_id
 * @property int $employee_id
 * @property float|null $share
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\HumanResources\Employee $employee
 * @property-read \App\Models\HumanResources\JobPosition $jobPosition
 * @method static Builder|EmployeeJobPosition newModelQuery()
 * @method static Builder|EmployeeJobPosition newQuery()
 * @method static Builder|EmployeeJobPosition query()
 * @method static Builder|EmployeeJobPosition whereCreatedAt($value)
 * @method static Builder|EmployeeJobPosition whereEmployeeId($value)
 * @method static Builder|EmployeeJobPosition whereId($value)
 * @method static Builder|EmployeeJobPosition whereJobPositionId($value)
 * @method static Builder|EmployeeJobPosition whereShare($value)
 * @method static Builder|EmployeeJobPosition whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class EmployeeJobPosition extends Pivot
{

    public $incrementing = true;

    protected $guarded = [];

    protected static function booted()
    {
        static::created(
            function (EmployeeJobPosition $employeeJobPosition) {
                HydrateEmployee::make()->updateJobPositionsShare($employeeJobPosition->employee);
                HydrateJobPosition::run(
                    $employeeJobPosition->jobPosition
                );

            }
        );
        static::deleted(
            function (EmployeeJobPosition $employeeJobPosition) {
                HydrateEmployee::make()->updateJobPositionsShare($employeeJobPosition->employee);
                HydrateJobPosition::run(
                    $employeeJobPosition->jobPosition
                );

            }
        );
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function jobPosition(): BelongsTo
    {
        return $this->belongsTo(JobPosition::class);
    }

}

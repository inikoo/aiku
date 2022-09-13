<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Mon, 12 Sept 2022 23:27:09 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Models;

use App\Models\HumanResources\JobPosition;
use App\Models\Organisations\Organisation;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;


/**
 * App\Models\JobPositionOrganisation
 *
 * @property int $id
 * @property int $job_position_id
 * @property int $organisation_id
 * @property int $number_employees
 * @property float $number_work_time
 * @property string|null $share_work_time
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read JobPosition $jobPosition
 * @property-read Organisation $organisation
 * @method static Builder|JobPositionOrganisation newModelQuery()
 * @method static Builder|JobPositionOrganisation newQuery()
 * @method static Builder|JobPositionOrganisation query()
 * @method static Builder|JobPositionOrganisation whereCreatedAt($value)
 * @method static Builder|JobPositionOrganisation whereId($value)
 * @method static Builder|JobPositionOrganisation whereJobPositionId($value)
 * @method static Builder|JobPositionOrganisation whereNumberEmployees($value)
 * @method static Builder|JobPositionOrganisation whereNumberWorkTime($value)
 * @method static Builder|JobPositionOrganisation whereOrganisationId($value)
 * @method static Builder|JobPositionOrganisation whereShareWorkTime($value)
 * @method static Builder|JobPositionOrganisation whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class JobPositionOrganisation extends Pivot
{
    public $incrementing = true;

    protected $guarded = [];

    public function organisation(): BelongsTo
    {
        return $this->belongsTo(Organisation::class);
    }

    public function jobPosition(): BelongsTo
    {
        return $this->belongsTo(JobPosition::class);
    }

}

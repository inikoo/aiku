<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 30 Dec 2023 14:29:00 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Models\HumanResources;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\HumanResources\WorkplaceStats
 *
 * @property int $id
 * @property int $workplace_id
 * @property int $number_clocking_machines
 * @property int $number_clocking_machines_type_biometric
 * @property int $number_clocking_machines_type_static_nfc
 * @property int $number_clocking_machines_type_mobile_app
 * @property int $number_clocking_machines_type_legacy
 * @property string|null $last_clocking_at
 * @property int $number_clockings
 * @property int $number_clockings_type_clocking_machine
 * @property int $number_clockings_type_manual
 * @property int $number_clockings_type_self_check
 * @property int $number_clockings_type_system
 * @property int $number_employees
 * @property int $number_employees_state_hired
 * @property int $number_employees_state_working
 * @property int $number_employees_state_leaving
 * @property int $number_employees_state_left
 * @property int $number_employees_type_employee
 * @property int $number_employees_type_volunteer
 * @property int $number_employees_type_temporal_worker
 * @property int $number_employees_type_work_experience
 * @property int $number_employees_gender_male
 * @property int $number_employees_gender_female
 * @property int $number_employees_gender_other
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\HumanResources\Workplace $workplace
 * @method static \Illuminate\Database\Eloquent\Builder|WorkplaceStats newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|WorkplaceStats newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|WorkplaceStats query()
 * @mixin \Eloquent
 */
class WorkplaceStats extends Model
{
    protected $guarded = [];

    public function workplace(): BelongsTo
    {
        return $this->belongsTo(Workplace::class);
    }
}

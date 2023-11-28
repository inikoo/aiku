<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 15 Sep 2023 16:06:07 Malaysia Time, Pantai Lembeng, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Models\Organisation;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\Organisation\OrganisationHumanResourcesStats
 *
 * @property int $id
 * @property int $organisation_id
 * @property int $number_employees
 * @property int $number_employees_state_hired
 * @property int $number_employees_state_working
 * @property int $number_employees_state_left
 * @property int $number_employees_type_employee
 * @property int $number_employees_type_volunteer
 * @property int $number_employees_type_temporal_worker
 * @property int $number_employees_type_work_experience
 * @property int $number_employees_gender_male
 * @property int $number_employees_gender_female
 * @property int $number_employees_gender_other
 * @property int $number_job_positions
 * @property int $number_workplaces
 * @property int $number_clocking_machines
 * @property int $number_clockings
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $number_workplaces_type_hq
 * @property int $number_workplaces_type_branch
 * @property int $number_workplaces_type_home
 * @property int $number_workplaces_type_group_premisses
 * @property int $number_workplaces_type_client_premises
 * @property int $number_workplaces_type_road
 * @property int $number_workplaces_type_other
 * @property-read \App\Models\Organisation\Organisation $organisation
 * @method static \Illuminate\Database\Eloquent\Builder|OrganisationHumanResourcesStats newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|OrganisationHumanResourcesStats newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|OrganisationHumanResourcesStats query()
 * @method static \Illuminate\Database\Eloquent\Builder|OrganisationHumanResourcesStats whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrganisationHumanResourcesStats whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrganisationHumanResourcesStats whereNumberClockingMachines($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrganisationHumanResourcesStats whereNumberClockings($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrganisationHumanResourcesStats whereNumberEmployees($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrganisationHumanResourcesStats whereNumberEmployeesGenderFemale($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrganisationHumanResourcesStats whereNumberEmployeesGenderMale($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrganisationHumanResourcesStats whereNumberEmployeesGenderOther($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrganisationHumanResourcesStats whereNumberEmployeesStateHired($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrganisationHumanResourcesStats whereNumberEmployeesStateLeft($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrganisationHumanResourcesStats whereNumberEmployeesStateWorking($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrganisationHumanResourcesStats whereNumberEmployeesTypeEmployee($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrganisationHumanResourcesStats whereNumberEmployeesTypeTemporalWorker($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrganisationHumanResourcesStats whereNumberEmployeesTypeVolunteer($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrganisationHumanResourcesStats whereNumberEmployeesTypeWorkExperience($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrganisationHumanResourcesStats whereNumberJobPositions($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrganisationHumanResourcesStats whereNumberWorkplaces($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrganisationHumanResourcesStats whereNumberWorkplacesTypeBranch($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrganisationHumanResourcesStats whereNumberWorkplacesTypeClientPremises($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrganisationHumanResourcesStats whereNumberWorkplacesTypeGroupPremisses($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrganisationHumanResourcesStats whereNumberWorkplacesTypeHome($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrganisationHumanResourcesStats whereNumberWorkplacesTypeHq($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrganisationHumanResourcesStats whereNumberWorkplacesTypeOther($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrganisationHumanResourcesStats whereNumberWorkplacesTypeRoad($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrganisationHumanResourcesStats whereOrganisationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrganisationHumanResourcesStats whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class GroupHumanResourcesStats extends Model
{
    protected $table = 'group_human_resources_stats';

    protected $guarded = [];

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }
}

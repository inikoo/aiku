<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 15 Nov 2023 13:13:54 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Models\Helpers;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Helpers\TagCrmStats
 *
 * @property int $id
 * @property int $tag_id
 * @property int $number_customers
 * @property int $number_customers_state_registered
 * @property int $number_customers_state_with_appointment
 * @property int $number_customers_state_contacted
 * @property int $number_customers_state_active
 * @property int $number_customers_state_losing
 * @property int $number_customers_state_lost
 * @property int $number_prospects
 * @property int $number_prospects_gender_male
 * @property int $number_prospects_gender_female
 * @property int $number_prospects_gender_other
 * @property int $number_prospects_state_no_contacted
 * @property int $number_prospects_state_contacted
 * @property int $number_prospects_state_fail
 * @property int $number_prospects_state_success
 * @property int $number_prospects_contacted_state_no_applicable
 * @property int $number_prospects_contacted_state_soft_bounced
 * @property int $number_prospects_contacted_state_never_open
 * @property int $number_prospects_contacted_state_open
 * @property int $number_prospects_contacted_state_clicked
 * @property int $number_prospects_fail_status_no_applicable
 * @property int $number_prospects_fail_status_not_interested
 * @property int $number_prospects_fail_status_unsubscribed
 * @property int $number_prospects_fail_status_hard_bounced
 * @property int $number_prospects_fail_status_invalid
 * @property int $number_prospects_success_status_no_applicable
 * @property int $number_prospects_success_status_registered
 * @property int $number_prospects_success_status_invoiced
 * @property int $number_prospects_dont_contact_me
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|TagCrmStats newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TagCrmStats newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TagCrmStats query()
 * @method static \Illuminate\Database\Eloquent\Builder|TagCrmStats whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TagCrmStats whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TagCrmStats whereNumberCustomers($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TagCrmStats whereNumberCustomersStateActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TagCrmStats whereNumberCustomersStateContacted($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TagCrmStats whereNumberCustomersStateLosing($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TagCrmStats whereNumberCustomersStateLost($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TagCrmStats whereNumberCustomersStateRegistered($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TagCrmStats whereNumberCustomersStateWithAppointment($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TagCrmStats whereNumberProspects($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TagCrmStats whereNumberProspectsContactedStateClicked($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TagCrmStats whereNumberProspectsContactedStateNeverOpen($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TagCrmStats whereNumberProspectsContactedStateNoApplicable($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TagCrmStats whereNumberProspectsContactedStateOpen($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TagCrmStats whereNumberProspectsContactedStateSoftBounced($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TagCrmStats whereNumberProspectsDontContactMe($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TagCrmStats whereNumberProspectsFailStatusHardBounced($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TagCrmStats whereNumberProspectsFailStatusInvalid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TagCrmStats whereNumberProspectsFailStatusNoApplicable($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TagCrmStats whereNumberProspectsFailStatusNotInterested($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TagCrmStats whereNumberProspectsFailStatusUnsubscribed($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TagCrmStats whereNumberProspectsGenderFemale($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TagCrmStats whereNumberProspectsGenderMale($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TagCrmStats whereNumberProspectsGenderOther($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TagCrmStats whereNumberProspectsStateContacted($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TagCrmStats whereNumberProspectsStateFail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TagCrmStats whereNumberProspectsStateNoContacted($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TagCrmStats whereNumberProspectsStateSuccess($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TagCrmStats whereNumberProspectsSuccessStatusInvoiced($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TagCrmStats whereNumberProspectsSuccessStatusNoApplicable($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TagCrmStats whereNumberProspectsSuccessStatusRegistered($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TagCrmStats whereTagId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TagCrmStats whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class TagCrmStats extends Model
{
    protected $table = 'tag_crm_stats';

    protected $guarded = [];
}

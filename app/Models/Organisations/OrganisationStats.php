<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 13 Sept 2022 12:39:33 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Models\Organisations;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\Organisations\OrganisationStats
 *
 * @property int $id
 * @property int $organisation_id
 * @property int $number_employees
 * @property int $number_employees_state_hired
 * @property int $number_employees_state_working
 * @property int $number_employees_state_left
 * @property int $number_guests
 * @property int $number_guests_status_active
 * @property int $number_guests_status_inactive
 * @property int $number_users
 * @property int $number_users_status_active
 * @property int $number_users_status_inactive
 * @property int $number_users_type_organisation
 * @property int $number_users_type_employee
 * @property int $number_users_type_guest
 * @property int $number_users_type_supplier
 * @property int $number_users_type_agent
 * @property int $number_users_type_customer
 * @property int $number_images
 * @property int $filesize_images
 * @property int $number_attachments
 * @property int $filesize_attachments
 * @property bool $has_fulfilment
 * @property bool $has_dropshipping
 * @property bool $has_production
 * @property bool $has_agents
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Organisations\Organisation $organisation
 * @method static \Illuminate\Database\Eloquent\Builder|OrganisationStats newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|OrganisationStats newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|OrganisationStats query()
 * @method static \Illuminate\Database\Eloquent\Builder|OrganisationStats whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrganisationStats whereFilesizeAttachments($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrganisationStats whereFilesizeImages($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrganisationStats whereHasAgents($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrganisationStats whereHasDropshipping($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrganisationStats whereHasFulfilment($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrganisationStats whereHasProduction($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrganisationStats whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrganisationStats whereNumberAttachments($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrganisationStats whereNumberEmployees($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrganisationStats whereNumberEmployeesStateHired($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrganisationStats whereNumberEmployeesStateLeft($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrganisationStats whereNumberEmployeesStateWorking($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrganisationStats whereNumberGuests($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrganisationStats whereNumberGuestsStatusActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrganisationStats whereNumberGuestsStatusInactive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrganisationStats whereNumberImages($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrganisationStats whereNumberUsers($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrganisationStats whereNumberUsersStatusActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrganisationStats whereNumberUsersStatusInactive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrganisationStats whereNumberUsersTypeAgent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrganisationStats whereNumberUsersTypeCustomer($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrganisationStats whereNumberUsersTypeEmployee($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrganisationStats whereNumberUsersTypeGuest($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrganisationStats whereNumberUsersTypeOrganisation($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrganisationStats whereNumberUsersTypeSupplier($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrganisationStats whereOrganisationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrganisationStats whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class OrganisationStats extends Model
{
    protected $table = 'organisation_stats';

    protected $guarded = [];

    public function organisation(): BelongsTo
    {
        return $this->belongsTo(Organisation::class);
    }
}

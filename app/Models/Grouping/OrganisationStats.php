<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 23 Apr 2023 11:32:21 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Models\Grouping;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\Grouping\OrganisationStats
 *
 * @property int $id
 * @property int $organisation_id
 * @property int $number_guests
 * @property int $number_guests_status_active
 * @property int $number_guests_status_inactive
 * @property int $number_users
 * @property int $number_users_status_active
 * @property int $number_users_status_inactive
 * @property int $number_users_type_employee
 * @property int $number_users_type_guest
 * @property int $number_users_type_supplier
 * @property int $number_users_type_agent
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
 * @property-read \App\Models\Grouping\Organisation $organisation
 * @method static \Illuminate\Database\Eloquent\Builder|OrganisationStats newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|OrganisationStats newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|OrganisationStats query()
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

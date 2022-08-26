<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Fri, 26 Aug 2022 15:58:05 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia F
 */

namespace App\Models\Delivery;

use App\Models\Organisations\Organisation;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * App\Models\Delivery\OrganisationShipper
 *
 * @property int $id
 * @property int $organisation_id
 * @property int $shipper_id
 * @property mixed $data
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read Organisation $organisation
 * @property-read \App\Models\Delivery\Shipper $shipper
 * @method static Builder|OrganisationShipper newModelQuery()
 * @method static Builder|OrganisationShipper newQuery()
 * @method static Builder|OrganisationShipper query()
 * @method static Builder|OrganisationShipper whereCreatedAt($value)
 * @method static Builder|OrganisationShipper whereData($value)
 * @method static Builder|OrganisationShipper whereId($value)
 * @method static Builder|OrganisationShipper whereOrganisationId($value)
 * @method static Builder|OrganisationShipper whereShipperId($value)
 * @method static Builder|OrganisationShipper whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class OrganisationShipper extends Pivot
{
    public $incrementing = true;

    protected $casts = [
        'data'   => 'array',
    ];

    protected $attributes = [
        'data' => '{}',
    ];

    public function shipper(): BelongsTo
    {
        return $this->belongsTo(Shipper::class);
    }

    public function organisation(): BelongsTo
    {
        return $this->belongsTo(Organisation::class);
    }

}

<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Fri, 26 Aug 2022 15:42:03 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia F
 */

namespace App\Models\Delivery;

use App\Models\Organisations\Organisation;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * App\Models\Delivery\Shipper
 *
 * @property int $id
 * @property string $code
 * @property bool $status
 * @property string $name
 * @property string|null $contact_name
 * @property string|null $company_name
 * @property string|null $email
 * @property string|null $phone
 * @property string|null $website
 * @property string|null $tracking_url
 * @property array $data
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection|Organisation[] $organisations
 * @property-read int|null $organisations_count
 * @method static Builder|Shipper newModelQuery()
 * @method static Builder|Shipper newQuery()
 * @method static Builder|Shipper query()
 * @method static Builder|Shipper whereCode($value)
 * @method static Builder|Shipper whereCompanyName($value)
 * @method static Builder|Shipper whereContactName($value)
 * @method static Builder|Shipper whereCreatedAt($value)
 * @method static Builder|Shipper whereData($value)
 * @method static Builder|Shipper whereDeletedAt($value)
 * @method static Builder|Shipper whereEmail($value)
 * @method static Builder|Shipper whereId($value)
 * @method static Builder|Shipper whereName($value)
 * @method static Builder|Shipper wherePhone($value)
 * @method static Builder|Shipper whereStatus($value)
 * @method static Builder|Shipper whereTrackingUrl($value)
 * @method static Builder|Shipper whereUpdatedAt($value)
 * @method static Builder|Shipper whereWebsite($value)
 * @mixin \Eloquent
 */
class Shipper extends Model
{
    protected $casts = [
        'data'   => 'array',
        'status' => 'boolean',
    ];

    protected $attributes = [
        'data' => '{}',
    ];

    protected $guarded = [];

    public function organisations(): BelongsToMany
    {
        return $this->belongsToMany(Organisation::class)->using(OrganisationShipper::class)->withTimestamps();
    }
}

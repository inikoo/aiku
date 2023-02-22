<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 23 Feb 2023 22:27:25 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Models\Dispatch;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;


/**
 * App\Models\Dispatch\Shipper
 *
 * @property int $id
 * @property string $slug
 * @property string $code
 * @property string|null $api_shipper
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
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property int|null $source_id
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Dispatch\Shipment> $shipments
 * @property-read int|null $shipments_count
 * @method static Builder|Shipper newModelQuery()
 * @method static Builder|Shipper newQuery()
 * @method static Builder|Shipper onlyTrashed()
 * @method static Builder|Shipper query()
 * @method static Builder|Shipper whereApiShipper($value)
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
 * @method static Builder|Shipper whereSlug($value)
 * @method static Builder|Shipper whereSourceId($value)
 * @method static Builder|Shipper whereStatus($value)
 * @method static Builder|Shipper whereTrackingUrl($value)
 * @method static Builder|Shipper whereUpdatedAt($value)
 * @method static Builder|Shipper whereWebsite($value)
 * @method static Builder|Shipper withTrashed()
 * @method static Builder|Shipper withoutTrashed()
 * @mixin \Eloquent
 */
class Shipper extends Model
{
    use HasSlug;
    use SoftDeletes;

    protected $casts = [
        'data'   => 'array',
        'status' => 'boolean',
    ];

    protected $attributes = [
        'data' => '{}',
    ];

    protected $guarded = [];

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('code')
            ->saveSlugsTo('slug');
    }

    public function shipments(): HasMany
    {
        return $this->hasMany(Shipment::class);
    }


}

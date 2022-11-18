<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Fri, 26 Aug 2022 15:42:03 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia F
 */

namespace App\Models\Delivery;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;


/**
 * App\Models\Delivery\Shipper
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
 * @method static Builder|Shipper newModelQuery()
 * @method static Builder|Shipper newQuery()
 * @method static \Illuminate\Database\Query\Builder|Shipper onlyTrashed()
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
 * @method static \Illuminate\Database\Query\Builder|Shipper withTrashed()
 * @method static \Illuminate\Database\Query\Builder|Shipper withoutTrashed()
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


}

<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 23 Feb 2023 22:27:25 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Models\Dispatch;

use App\Models\Search\UniversalSearch;
use App\Models\Traits\HasUniversalSearch;
use Database\Factories\Dispatch\ShipmentFactory;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

/**
 * App\Models\Dispatch\Shipment
 *
 * @property int $id
 * @property string $slug
 * @property string|null $code
 * @property int|null $shipper_id
 * @property string|null $tracking
 * @property array $data
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property int|null $source_id
 * @property-read Shipper|null $shipper
 * @property-read UniversalSearch|null $universalSearch
 * @method static ShipmentFactory factory($count = null, $state = [])
 * @method static Builder|Shipment newModelQuery()
 * @method static Builder|Shipment newQuery()
 * @method static Builder|Shipment onlyTrashed()
 * @method static Builder|Shipment query()
 * @method static Builder|Shipment withTrashed()
 * @method static Builder|Shipment withoutTrashed()
 * @mixin Eloquent
 */
class PdfLabel extends Model
{
    use SoftDeletes;
    use HasSlug;
    use UsesTenantConnection;
    use HasUniversalSearch;
    use HasFactory;

    protected $casts = [
        'data' => 'array',
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

    public function shipper(): BelongsTo
    {
        return $this->belongsTo(Shipper::class);
    }

}

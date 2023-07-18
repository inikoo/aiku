<?php

namespace App\Models\Fulfilment;

use App\Enums\Fulfilment\StoredItem\StoredItemStateEnum;
use App\Models\Traits\HasUniversalSearch;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;

/**
 * App\Models\Fulfilment\StoredItem
 *
 * @property int $id
 * @property string $slug
 * @property string $code
 * @property string $status
 * @property StoredItemStateEnum $state
 * @property int $customer_id
 * @property int $location_id
 * @property string $notes
 * @property bool $return_requested
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string|null $received_at
 * @property string|null $booked_in_at
 * @property string|null $settled_at
 * @property array $data
 * @property string|null $deleted_at
 * @property int|null $source_id
 * @property-read \App\Models\Search\UniversalSearch|null $universalSearch
 * @method static Builder|StoredItem newModelQuery()
 * @method static Builder|StoredItem newQuery()
 * @method static Builder|StoredItem query()
 * @mixin Eloquent
 */
class StoredItem extends Model
{
    use UsesTenantConnection;
    use HasUniversalSearch;

    protected $guarded = [];

    protected $casts = [
        'data'  => 'array',
        'state' => StoredItemStateEnum::class
    ];

    protected $attributes = [
        'data' => '{}',
    ];
}

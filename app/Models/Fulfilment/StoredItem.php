<?php

namespace App\Models\Fulfilment;

use App\Enums\Fulfilment\StoredItem\StoredItemStateEnum;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;

/**
 * App\Models\Fulfilment\StoredItem
 *
 * @property int $id
 * @property string $slug
 * @property string $code
 * @property bool $status false for returned goods
 * @property StoredItemStateEnum $state
 * @property int $customer_id
 * @property int $location_id
 * @property string $notes
 * @property bool $return_requested
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $received_at
 * @property string|null $stored_at
 * @property string|null $returned_at
 * @property array $data
 * @property string|null $deleted_at
 * @property int|null $source_id
 * @method static Builder|StoredItem newModelQuery()
 * @method static Builder|StoredItem newQuery()
 * @method static Builder|StoredItem query()
 * @mixin \Eloquent
 */
class StoredItem extends Model
{
    use UsesTenantConnection;

    protected $guarded = [];

    protected $casts = [
        'data'  => 'array',
        'state' => StoredItemStateEnum::class
    ];

    protected $attributes = [
        'data' => '{}',
    ];
}

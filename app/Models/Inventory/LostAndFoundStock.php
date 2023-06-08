<?php

namespace App\Models\Inventory;

use App\Enums\Inventory\Stock\LostAndFoundStockStateEnum;
use App\Models\Search\UniversalSearch;
use App\Models\Traits\HasUniversalSearch;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;

/**
 * App\Models\Inventory\LostAndFoundStock
 *
 * @property int $id
 * @property int $location_id
 * @property string $code
 * @property string $quantity
 * @property string $stock_value
 * @property string $type
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property LostAndFoundStockStateEnum $state
 * @property-read UniversalSearch|null $universalSearch
 * @method static \Database\Factories\Inventory\LostAndFoundStockFactory factory($count = null, $state = [])
 * @method static Builder|LostAndFoundStock newModelQuery()
 * @method static Builder|LostAndFoundStock newQuery()
 * @method static Builder|LostAndFoundStock query()
 * @mixin Eloquent
 */

class LostAndFoundStock extends Model
{
    use UsesTenantConnection;
    use HasUniversalSearch;
    use HasFactory;

    protected $casts = [
        'state' => LostAndFoundStockStateEnum::class,
    ];

    protected $guarded = [];
}

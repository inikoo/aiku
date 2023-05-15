<?php

namespace App\Models\Inventory;

use App\Enums\Inventory\Stock\LostAndFoundStockStateEnum;
use App\Models\Traits\HasUniversalSearch;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;

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

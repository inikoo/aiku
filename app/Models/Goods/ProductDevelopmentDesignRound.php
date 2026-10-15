<?php

/*
 * Author Louis Perez
 * Created on 15-10-2026-16h-10m
 * GitHub: https://github.com/louis-perez
 * Copyright 2026
*/

namespace App\Models\Goods;

use App\Enums\Goods\ProductDevelopment\ProductDevelopmentDesignRoundEnum;
use App\Enums\Goods\ProductDevelopment\ProductDevelopmentDesignRoundStatusEnum;
use App\Models\SysAdmin\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $product_development_id
 * @property int|null $product_development_item_id
 * @property ProductDevelopmentDesignRoundEnum $round
 * @property int $version
 * @property ProductDevelopmentDesignRoundStatusEnum $status
 * @property int|null $approved_by_id
 * @property Carbon|null $approved_at
 * @property Carbon|null $locked_at
 * @property-read ProductDevelopment|null $productDevelopment
 * @mixin \Eloquent
 */
class ProductDevelopmentDesignRound extends Model
{
    protected $table = 'product_development_design_rounds';

    protected $casts = [
        'round'       => ProductDevelopmentDesignRoundEnum::class,
        'status'      => ProductDevelopmentDesignRoundStatusEnum::class,
        'approved_at' => 'datetime',
        'locked_at'   => 'datetime',
    ];

    protected $guarded = [];

    public function productDevelopment(): BelongsTo
    {
        return $this->belongsTo(ProductDevelopment::class);
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by_id');
    }

    public function files(): HasMany
    {
        return $this->hasMany(ProductDevelopmentFile::class, 'design_round_id');
    }
}

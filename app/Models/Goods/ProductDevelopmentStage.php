<?php

/*
 * Author Louis Perez
 * Created on 15-10-2026-16h-10m
 * GitHub: https://github.com/louis-perez
 * Copyright 2026
*/

namespace App\Models\Goods;

use App\Enums\Goods\ProductDevelopment\ProductDevelopmentMemberRoleEnum;
use App\Enums\Goods\ProductDevelopment\ProductDevelopmentStageEnum;
use App\Enums\Goods\ProductDevelopment\ProductDevelopmentStageStateEnum;
use App\Models\SysAdmin\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $product_development_id
 * @property int $position
 * @property string|null $sub_position
 * @property ProductDevelopmentStageEnum $stage
 * @property ProductDevelopmentMemberRoleEnum $role
 * @property ProductDevelopmentStageStateEnum $state
 * @property Carbon|null $due_at
 * @property Carbon|null $started_at
 * @property Carbon|null $completed_at
 * @property int|null $completed_by_id
 * @property-read ProductDevelopment|null $productDevelopment
 * @property-read User|null $completedBy
 * @mixin \Eloquent
 */
class ProductDevelopmentStage extends Model
{
    protected $table = 'product_development_stages';

    protected $casts = [
        'stage'        => ProductDevelopmentStageEnum::class,
        'role'         => ProductDevelopmentMemberRoleEnum::class,
        'state'        => ProductDevelopmentStageStateEnum::class,
        'due_at'       => 'date',
        'started_at'   => 'datetime',
        'completed_at' => 'datetime',
    ];

    protected $guarded = [];

    public function productDevelopment(): BelongsTo
    {
        return $this->belongsTo(ProductDevelopment::class);
    }

    public function completedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'completed_by_id');
    }
}

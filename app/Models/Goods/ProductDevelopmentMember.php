<?php

/*
 * Author Louis Perez
 * Created on 15-10-2026-16h-10m
 * GitHub: https://github.com/louis-perez
 * Copyright 2026
*/

namespace App\Models\Goods;

use App\Enums\Goods\ProductDevelopment\ProductDevelopmentMemberRoleEnum;
use App\Models\SysAdmin\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $product_development_id
 * @property int $user_id
 * @property ProductDevelopmentMemberRoleEnum $role
 * @property-read ProductDevelopment|null $productDevelopment
 * @property-read User|null $user
 * @mixin \Eloquent
 */
class ProductDevelopmentMember extends Model
{
    protected $table = 'product_development_members';

    protected $casts = [
        'role' => ProductDevelopmentMemberRoleEnum::class,
    ];

    protected $guarded = [];

    public function productDevelopment(): BelongsTo
    {
        return $this->belongsTo(ProductDevelopment::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

<?php

/*
 * Author Louis Perez
 * Created on 15-10-2026-16h-10m
 * GitHub: https://github.com/louis-perez
 * Copyright 2026
*/

namespace App\Models\Goods;

use App\Enums\Goods\ProductDevelopment\ProductDevelopmentCommentStatusEnum;
use App\Models\SysAdmin\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $product_development_id
 * @property string|null $model_type
 * @property int|null $model_id
 * @property int|null $parent_id
 * @property int $user_id
 * @property string $body
 * @property array<array-key, mixed> $mentions
 * @property array<array-key, mixed> $data
 * @property ProductDevelopmentCommentStatusEnum|null $status
 * @property int|null $resolved_by_id
 * @property Carbon|null $resolved_at
 * @property-read ProductDevelopment|null $productDevelopment
 * @property-read User|null $user
 * @mixin \Eloquent
 */
class ProductDevelopmentComment extends Model
{
    use SoftDeletes;

    protected $table = 'product_development_comments';

    protected $casts = [
        'mentions'    => 'array',
        'data'        => 'array',
        'status'      => ProductDevelopmentCommentStatusEnum::class,
        'resolved_at' => 'datetime',
    ];

    protected $attributes = [
        'mentions' => '[]',
        'data'     => '{}',
    ];

    protected $guarded = [];

    public function productDevelopment(): BelongsTo
    {
        return $this->belongsTo(ProductDevelopment::class);
    }

    public function model(): MorphTo
    {
        return $this->morphTo();
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

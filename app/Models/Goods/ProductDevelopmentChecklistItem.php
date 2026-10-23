<?php

/*
 * Author Louis Perez
 * Created on 15-10-2026-16h-10m
 * GitHub: https://github.com/louis-perez
 * Copyright 2026
*/

namespace App\Models\Goods;

use App\Enums\Goods\ProductDevelopment\ProductDevelopmentChecklistItemStateEnum;
use App\Enums\Goods\ProductDevelopment\ProductDevelopmentChecklistLevelEnum;
use App\Enums\Goods\ProductDevelopment\ProductDevelopmentMemberRoleEnum;
use App\Models\SysAdmin\User;
use App\Models\Traits\HasHistory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * @property int $id
 * @property int $product_development_id
 * @property int|null $product_development_item_id
 * @property int|null $template_id
 * @property ProductDevelopmentMemberRoleEnum $role
 * @property ProductDevelopmentChecklistLevelEnum $level
 * @property string $key
 * @property string $label
 * @property bool $required
 * @property string|null $target_field
 * @property ProductDevelopmentChecklistItemStateEnum $state
 * @property array<array-key, mixed>|null $value
 * @property string|null $na_reason
 * @property int|null $completed_by_id
 * @property Carbon|null $completed_at
 * @property-read ProductDevelopment|null $productDevelopment
 * @property-read ProductDevelopmentItem|null $item
 * @property-read User|null $completedBy
 * @mixin \Eloquent
 */
class ProductDevelopmentChecklistItem extends Model implements Auditable
{
    use HasHistory;

    protected $table = 'product_development_checklist_items';

    protected $casts = [
        'role'         => ProductDevelopmentMemberRoleEnum::class,
        'level'        => ProductDevelopmentChecklistLevelEnum::class,
        'state'        => ProductDevelopmentChecklistItemStateEnum::class,
        'required'     => 'boolean',
        'value'        => 'array',
        'completed_at' => 'datetime',
    ];

    protected $guarded = [];

    protected array $auditInclude = [
        'state',
        'value',
        'na_reason',
    ];

    public function generateTags(): array
    {
        return [
            'goods',
        ];
    }

    public function productDevelopment(): BelongsTo
    {
        return $this->belongsTo(ProductDevelopment::class);
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(ProductDevelopmentItem::class, 'product_development_item_id');
    }

    public function completedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'completed_by_id');
    }

    public function isMissing(): bool
    {
        return $this->required && $this->state === ProductDevelopmentChecklistItemStateEnum::MISSING;
    }
}

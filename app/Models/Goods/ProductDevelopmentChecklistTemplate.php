<?php

/*
 * Author Louis Perez
 * Created on 15-10-2026-16h-10m
 * GitHub: https://github.com/louis-perez
 * Copyright 2026
*/

namespace App\Models\Goods;

use App\Enums\Goods\ProductDevelopment\ProductDevelopmentChecklistLevelEnum;
use App\Enums\Goods\ProductDevelopment\ProductDevelopmentMemberRoleEnum;
use App\Models\Traits\InGroup;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $group_id
 * @property string $category
 * @property ProductDevelopmentMemberRoleEnum $role
 * @property ProductDevelopmentChecklistLevelEnum $level
 * @property string $key
 * @property string $label
 * @property bool $required
 * @property string|null $target_field
 * @property int $position
 * @property bool $is_active
 * @mixin \Eloquent
 */
class ProductDevelopmentChecklistTemplate extends Model
{
    use InGroup;

    protected $table = 'product_development_checklist_templates';

    protected $casts = [
        'role'      => ProductDevelopmentMemberRoleEnum::class,
        'level'     => ProductDevelopmentChecklistLevelEnum::class,
        'required'  => 'boolean',
        'is_active' => 'boolean',
    ];

    protected $guarded = [];
}

<?php

/*
 * Author Louis Perez
 * Created on 15-10-2026-16h-10m
 * GitHub: https://github.com/louis-perez
 * Copyright 2026
*/

namespace App\Models\Goods;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $product_development_id
 * @property int $number_items
 * @property int $number_checklist_items
 * @property int $number_required_missing
 * @property int $number_required_missing_buyer
 * @property int $number_required_missing_agent
 * @property int $number_required_missing_purchasing
 * @property int $number_required_missing_compliance
 * @property int $number_required_missing_designer
 * @property int $number_required_missing_webmaster
 * @property int $number_required_missing_approver
 * @property int $number_open_comments
 * @property-read ProductDevelopment|null $productDevelopment
 * @mixin \Eloquent
 */
class ProductDevelopmentStats extends Model
{
    protected $table = 'product_development_stats';

    protected $guarded = [];

    public function productDevelopment(): BelongsTo
    {
        return $this->belongsTo(ProductDevelopment::class);
    }
}

<?php

/*
 * Author Louis Perez
 * Created on 22-10-2026-14h-00m
 * GitHub: https://github.com/louis-perez
 * Copyright 2026
*/

namespace App\Models\Goods;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * A packaging or print piece of a development (Box, Label, Tag, Sleeve…) with one artwork per SKU.
 *
 * @property int $id
 * @property int $product_development_id
 * @property string $name
 * @property string|null $description
 * @property string|null $packaging_supplier
 * @property string $editor_kind box or sheet: which Aiku editor an artwork built here opens
 * @property bool $needs_compliance_text its artwork can only be approved once the SKU's compliance text is
 * @property int $position
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read ProductDevelopment|null $productDevelopment
 * @property-read \Illuminate\Database\Eloquent\Collection<int, ProductDevelopmentArtwork> $artworks
 * @mixin \Eloquent
 */
class ProductDevelopmentDesignSection extends Model
{
    protected $table = 'product_development_design_sections';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'needs_compliance_text' => 'boolean',
        ];
    }

    public function productDevelopment(): BelongsTo
    {
        return $this->belongsTo(ProductDevelopment::class);
    }

    public function artworks(): HasMany
    {
        return $this->hasMany(ProductDevelopmentArtwork::class, 'design_section_id');
    }
}

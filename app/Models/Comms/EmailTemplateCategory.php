<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 19 Nov 2024 11:11:46 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Models\Comms;

use App\Actions\Utils\Abbreviate;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

/**
 * App\Models\Mail\EmailTemplateCategory
 *
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Comms\EmailTemplate> $templates
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmailTemplateCategory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmailTemplateCategory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmailTemplateCategory query()
 * @mixin \Eloquent
 */
class EmailTemplateCategory extends Model
{
    use HasFactory;
    use HasSlug;

    protected $guarded = [];

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom(function () {
                if (mb_strlen($this->name) >= 8) {
                    return Abbreviate::run($this->name);
                } else {
                    return  $this->name;
                }
            })
            ->saveSlugsTo('slug')
            ->slugsShouldBeNoLongerThan(12)
            ->doNotGenerateSlugsOnUpdate();

    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }


    public function templates(): BelongsToMany
    {
        return $this->belongsToMany(EmailTemplate::class, EmailTemplatePivotEmailCategory::class);
    }
}

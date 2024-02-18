<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 17 Feb 2024 01:36:01 Malaysia Time, Mexico City, Mexico
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Models\Traits;

use App\Actions\Helpers\Images\GetPictureSources;
use App\Models\Assets\Language;
use App\Models\Media\Media;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Sanctum\HasApiTokens;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Sluggable\HasSlug;

trait IsUserable
{
    use HasApiTokens;
    use SoftDeletes;
    use HasSlug;
    use HasFactory;
    use HasUniversalSearch;
    use InteractsWithMedia;
    use HasHistory;



    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('avatar')
            ->singleFile();
    }

    public function avatar(): HasOne
    {
        return $this->hasOne(Media::class, 'id', 'avatar_id');
    }

    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class);
    }
    public function avatarImageSources($width = 0, $height = 0)
    {
        if($this->avatar) {
            $avatarThumbnail = $this->avatar->getImage()->resize($width, $height);
            return GetPictureSources::run($avatarThumbnail);
        }
        return null;
    }

}

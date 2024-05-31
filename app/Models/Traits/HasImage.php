<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 30 May 2024 08:37:52 Central European Summer Time, Mijas Costa, Spain
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Models\Traits;

use App\Actions\Helpers\Images\GetPictureSources;
use App\Models\Studio\Media;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Spatie\MediaLibrary\InteractsWithMedia;

trait HasImage
{
    use InteractsWithMedia;

    public function images(): MorphToMany
    {
        return $this->morphToMany(Media::class, 'model', 'model_has_media')->withTimestamps();
    }
    public function image(): HasOne
    {
        return $this->hasOne(Media::class, 'id', 'image_id');
    }

    public function imageSources($width = 0, $height = 0)
    {
        if($this->image) {
            $avatarThumbnail = $this->image->getImage()->resize($width, $height);
            return GetPictureSources::run($avatarThumbnail);
        }
        return null;
    }

}

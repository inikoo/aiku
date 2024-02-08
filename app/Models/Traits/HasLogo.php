<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 17 Oct 2023 20:03:14 Malaysia Time, Office, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Models\Traits;

use App\Actions\Helpers\Images\GetPictureSources;
use App\Models\Media\Media;
use Illuminate\Database\Eloquent\Relations\HasOne;

trait HasLogo
{
    public function logo(): HasOne
    {
        return $this->hasOne(Media::class, 'id', 'logo_id');
    }

    public function logoImageSources($width = 0, $height = 0)
    {
        if($this->logo) {
            $logoThumbnail = $this->logo->getImage()->resize($width, $height);
            return GetPictureSources::run($logoThumbnail);
        }
        return null;
    }



}

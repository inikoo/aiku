<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 18 Sep 2023 18:42:14 Malaysia Time, Pantai Lembeng, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Portfolio\Slide;

use App\Actions\Traits\WithActionUpdate;
use App\Models\Portfolio\Slide;

class UpdateSlide
{
    use WithActionUpdate;


    public function handle(Slide $slide, array $modelData): Slide
    {

        $this->update($slide, $modelData, ['layout']);


        return $slide;
    }


}

<?php

namespace App\Actions\Web\Website;

use App\Models\Web\Website;
use Illuminate\Support\Arr;
use Lorisleiva\Actions\Concerns\AsObject;

class GetWebsiteWorkshopMenu
{
    use AsObject;

    public function handle(Website $website): array
    {
        return [
            'header'  => Arr::get($website->published_layout, 'header'),
            'color'   => Arr::get($website->published_layout, 'color'),
            'menu'    => Arr::get($website->published_layout, 'menu')
        ];
    }
}

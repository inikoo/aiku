<?php

namespace App\Actions\Web\Website;

use App\Models\Web\Website;
use Lorisleiva\Actions\Concerns\AsObject;

class GetWebsiteWorkshopMenuPreview
{
    use AsObject;

    public function handle(Website $website): array
    {
        return [
            'menu'    => $website->unpublishedHeaderSnapshot // need fix later
        ];
    }
}

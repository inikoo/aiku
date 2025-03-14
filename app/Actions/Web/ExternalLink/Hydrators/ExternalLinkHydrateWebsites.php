<?php

/*
 * Author: Ganes <gustiganes@gmail.com>
 * Created on: 27-12-2024, Bali, Indonesia
 * Github: https://github.com/Ganes556
 * Copyright: 2024
 *
*/

namespace App\Actions\Web\ExternalLink\Hydrators;

use App\Models\Web\ExternalLink;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Lorisleiva\Actions\Concerns\AsAction;

class ExternalLinkHydrateWebsites
{
    use AsAction;
    private ExternalLink $externalLink;

    public function __construct(ExternalLink $externalLink)
    {
        $this->externalLink = $externalLink;
    }

    public function getJobMiddleware(): array
    {
        return [(new WithoutOverlapping($this->externalLink->id))->dontRelease()];
    }

    public function handle(ExternalLink $externalLink): void
    {
        $data = [
            'number_websites_shown'    => $externalLink->websites()->wherePivot('show', true)->count(),
            'number_websites_hidden'   => $externalLink->websites()->wherePivot('show', false)->count(),
        ];

        $externalLink->update($data);
    }
}

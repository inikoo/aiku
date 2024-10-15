<?php
/*
 * Author: Ganes <gustiganes@gmail.com>
 * Created on: 15-10-2024, Bali, Indonesia
 * Github: https://github.com/aqordeon
 * Copyright: 2024
 *
*/

namespace App\Actions\Web\Website\Hydrators;

use App\Actions\Traits\WithEnumStats;
use App\Enums\Web\Redirect\RedirectTypeEnum;
use App\Models\Web\Redirect;
use App\Models\Web\Website;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Lorisleiva\Actions\Concerns\AsAction;

class WebsiteHydrateRedirects
{
    use AsAction;
    use WithEnumStats;
    private Website $website;

    public function __construct(Website $website)
    {
        $this->website = $website;
    }

    public function getJobMiddleware(): array
    {
        return [(new WithoutOverlapping($this->website->id))->dontRelease()];
    }

    public function handle(Website $website): void
    {
        $stats = [
            'number_redirects' => $website->redirects()->count(),
        ];

        $stats = array_merge(
            $stats,
            $this->getEnumStats(
                model: 'redirects',
                field: 'type',
                enum: RedirectTypeEnum::class,
                models: Redirect::class,
                where: function ($q) use ($website) {
                    $q->where('website_id', $website->id);
                }
            )
        );

        $website->webStats()->update($stats);
    }
}

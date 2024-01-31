<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 07 Jun 2023 01:12:55 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Web\Website\Hydrators;

use App\Actions\Traits\WithEnumStats;
use App\Enums\Web\Webpage\WebpagePurposeEnum;
use App\Enums\Web\Webpage\WebpageStateEnum;
use App\Enums\Web\Webpage\WebpageTypeEnum;
use App\Models\Web\Webpage;
use App\Models\Web\Website;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Lorisleiva\Actions\Concerns\AsAction;

class WebsiteHydrateWebpages
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
            'number_webpages' => $website->webpages->count(),
        ];

        $stats=array_merge($stats, $this->getEnumStats(
            model:'webpages',
            field: 'state',
            enum: WebpageStateEnum::class,
            models: Webpage::class,
            where: function ($q) use ($website) {
                $q->where('website_id', $website->id);
            }
        ));

        $stats=array_merge($stats, $this->getEnumStats(
            model:'webpages',
            field: 'type',
            enum: WebpageTypeEnum::class,
            models: Webpage::class,
            where: function ($q) use ($website) {
                $q->where('website_id', $website->id);
            }
        ));

        $stats=array_merge($stats, $this->getEnumStats(
            model:'webpages',
            field: 'purpose',
            enum: WebpagePurposeEnum::class,
            models: Webpage::class,
            where: function ($q) use ($website) {
                $q->where('website_id', $website->id);
            }
        ));

        $website->webStats()->update($stats);
    }


}

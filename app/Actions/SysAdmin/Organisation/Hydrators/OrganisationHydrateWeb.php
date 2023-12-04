<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 04 Dec 2023 16:15:10 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\SysAdmin\Organisation\Hydrators;

use App\Enums\Web\Website\WebsiteEngineEnum;
use App\Enums\Web\Website\WebsiteStateEnum;
use App\Enums\Web\Website\WebsiteTypeEnum;
use App\Models\SysAdmin\Organisation;
use App\Models\Web\WebpageVariant;
use App\Models\Web\Website;
use Arr;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Lorisleiva\Actions\Concerns\AsAction;

class OrganisationHydrateWeb implements ShouldBeUnique
{
    use AsAction;


    public function handle(Organisation $organisation): void
    {
        $stats = [
            'number_websites' => Website::count(),
            'number_webpages' => WebpageVariant::count()
        ];

        $websiteStateCount = Website::selectRaw('state, count(*) as total')
            ->groupBy('state')
            ->pluck('total', 'state')->all();


        foreach (WebsiteStateEnum::cases() as $websiteState) {
            $stats['number_websites_state_'.$websiteState->snake()] = Arr::get($websiteStateCount, $websiteState->value, 0);
        }

        $websiteEngineCount = Website::selectRaw('engine, count(*) as total')
            ->groupBy('engine')
            ->pluck('total', 'engine')->all();

        foreach (WebsiteEngineEnum::cases() as $websiteEngine) {
            $stats['number_websites_engine_'.$websiteEngine->snake()] = Arr::get($websiteEngineCount, $websiteEngine->value, 0);
        }

        $websiteTypeCount = Website::selectRaw('type, count(*) as total')
            ->groupBy('type')
            ->pluck('total', 'type')->all();

        foreach (WebsiteTypeEnum::cases() as $websiteType) {
            $stats['number_websites_type_'.$websiteType->snake()] = Arr::get($websiteTypeCount, $websiteType->value, 0);
        }


        $organisation->webStats()->update($stats);
    }
}

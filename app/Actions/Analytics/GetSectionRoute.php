<?php
/*
 * Author: Ganes <gustiganes@gmail.com>
 * Created on: 21-11-2024, Bali, Indonesia
 * Github: https://github.com/Ganes556
 * Copyright: 2024
 *
*/

namespace App\Actions\Analytics;

use App\Actions\OrgAction;
use App\Enums\Analytics\AikuSection\AikuSectionEnum;
use App\Models\Analytics\AikuScopedSection;
use Lorisleiva\Actions\Concerns\AsController;

class GetSectionRoute extends OrgAction
{
    use AsController;

    public function handle(string $routeName, array $routeParameters): string
    {
        // if (str_starts_with($routeName, 'grp.org.')) {
        //     return $this->parseOrganisationSections(
        //         preg_replace('/^grp\.org./', '', $routeName)
        //     );
        // }

        // grp.org.shops.show.dashboard

        if ($routeName == "grp.org.shops.show.dashboard") {
            AikuScopedSection::where('code', AikuSectionEnum::SHOP_DASHBOARD)->where('model_slug', $routeParameters['shop']);
        }


        return "";
    }
}

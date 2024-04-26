<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Thu, 25 Apr 2024 16:56:22 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Web\Webpage\UI;

use App\Actions\OrgAction;
use App\Actions\Web\Website\GetWebsiteWorkshopFooter;
use App\Models\Market\Shop;
use App\Models\SysAdmin\Organisation;
use App\Models\Web\Webpage;
use App\Models\Web\Website;
use Inertia\Inertia;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

class ShowFooter extends OrgAction
{
    use AsAction;


    private Website $website;

    private Webpage|Website $parent;

    public function handle(Website $website): Website
    {
        return $website;
    }

    public function htmlResponse(Website $website, ActionRequest $request)
    {
        return Inertia::render(
            'Org/Web/Webpage',
            [
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->getName(),
                    $request->route()->originalParameters()
                ),
                'title'       => __('webpage'),
                'pageHead'    => [
                    'title'    => $website->code,
                    'icon'     => [
                        'title' => __('website'),
                        'icon'  => 'fal fa-browser'
                    ],
                ],

                'data' => GetWebsiteWorkshopFooter::run($website)
            ]
        );
    }

    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }

        return $request->user()->hasPermissionTo("web.{$this->shop->id}.edit");
    }

    public function asController(Organisation $organisation, Shop $shop, Website $website, ActionRequest $request): Website
    {
        $this->parent = $website;
        $this->initialisationFromShop($shop, $request);

        return $website;
    }

}

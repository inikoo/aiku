<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Wed, 12 Oct 2022 17:04:31 Central European Summer, Benalmádena, Malaga, Spain
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\Web\Website;

use App\Actions\InertiaAction;
use App\Actions\Marketing\Shop\IndexShops;
use App\Actions\UI\WithInertia;
use App\Http\Resources\Marketing\WebsiteResource;
use App\Models\Marketing\Shop;
use App\Models\Web\Website;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use JetBrains\PhpStorm\Pure;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

class ShowWebsite extends InertiaAction
{
    use AsAction;
    use WithInertia;

    public function handle(Website $website): Website
    {
        return $website;
    }

    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->hasPermissionTo("shops.products.view");
    }

    public function asController(Website $website): Website
    {
        return $this->handle($website);
    }

    public function inShop(Shop $shop, Website $website, Request $request): Website
    {
        $this->routeName = $request->route()->getName();
        $this->validateAttributes();

        return $this->handle($website);
    }

    public function htmlResponse(Website $website): Response
    {
        $this->validateAttributes();


        return Inertia::render(
            'Marketing/Website',
            [
                'title'       => __('Website'),
                'breadcrumbs' => $this->getBreadcrumbs($website),
                'pageHead'    => [
                    'title' => $website->number,


                ],
                'Website' => new WebsiteResource($website),
            ]
        );
    }

    public function prepareForValidation(ActionRequest $request): void
    {
        $this->fillFromRequest($request);

        $this->set('canEdit', $request->user()->can('hr.edit'));
        $this->set('canViewUsers', $request->user()->can('users.view'));
    }

    #[Pure] public function jsonResponse(Website $website): WebsiteResource
    {
        return new WebsiteResource($website);
    }


    public function getBreadcrumbs(Website $website): array
    {
        return array_merge(
            (new IndexShops())->getBreadcrumbs(),
            [
                'shops.show' => [
                    'route'           => 'shops.show',
                    'routeParameters' => $website->id,
                    'name'            => $website->number,
                    'index'           => [
                        'route'   => 'shops.index',
                        'overlay' => __('Websites list')
                    ],
                    'modelLabel' => [
                        'label' => __('Website')
                    ],
                ],
            ]
        );
    }
}

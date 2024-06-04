<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 21 Sep 2023 11:35:41 Malaysia Time, Pantai Lembeng, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Web\Website\UI;

use App\Actions\InertiaAction;
use App\Enums\UI\Web\WebsiteWorkshopTabsEnum;
use App\Models\Web\Website;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class ShowWebsiteWorkshopPreview extends InertiaAction
{
    public function authorize(ActionRequest $request): bool
    {
        $this->canEdit   = $request->user()->hasPermissionTo('websites.edit');
        $this->canDelete = $request->user()->hasPermissionTo('websites.edit');

        return $request->user()->hasPermissionTo("websites.edit");
    }

    public function asController(Website $website, ActionRequest $request): Website
    {
        $this->initialisation($request)->withTab(WebsiteWorkshopTabsEnum::values());

        return $website;
    }




    public function htmlResponse(Website $website, ActionRequest $request): Response
    {
        return Inertia::render(
            'Web/PreviewWorkshop',
            [
                'title'       => __("Website's preview"),
                'breadcrumbs' => $this->getBreadcrumbs($request->route()->originalParameters()),
                'pageHead'    => [

                    'title' => __('Preview'),

                    'actions' => [
                        [
                            'type'  => 'button',
                            'style' => 'exit',
                            'label' => __('Exit Preview'),
                            'route' => [
                                'name'       => preg_replace('/preview$/', 'show', $request->route()->getName()),
                                'parameters' => array_values($request->route()->originalParameters()),
                            ]
                        ],

                        $this->canEdit ? [
                            'type'  => 'button',
                            'style' => 'edit',
                            'label' => __('workshop'),
                            'icon'  => ["fal", "fa-drafting-compass"],
                            'route' => [
                                'name'       => preg_replace('/preview/', 'workshop', $request->route()->getName()),
                                'parameters' => array_values($request->route()->originalParameters())
                            ]
                        ] : [],
                    ],
                ],
            ]
        );
    }

    public function getBreadcrumbs(array $routeParameters): array
    {
        return ShowWebsite::make()->getBreadcrumbs(
            'Shop',
            $routeParameters,
            suffix: '('.__('preview').')'
        );
    }



}

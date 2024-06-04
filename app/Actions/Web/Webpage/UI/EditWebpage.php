<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 21 Sep 2023 11:35:41 Malaysia Time, Pantai Lembeng, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Web\Webpage\UI;

use App\Actions\OrgAction;
use App\Actions\Traits\Authorisations\HasWebAuthorisation;
use App\Models\Catalogue\Shop;
use App\Models\Fulfilment\Fulfilment;
use App\Models\SysAdmin\Organisation;
use App\Models\Web\Webpage;
use App\Models\Web\Website;
use Exception;
use Arr;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class EditWebpage extends OrgAction
{
    use HasWebAuthorisation;


    public function handle(Webpage $webpage): Webpage
    {
        return $webpage;
    }


    public function asController(Organisation $organisation, Shop $shop, Website $website, Webpage $webpage, ActionRequest $request): Webpage
    {
        $this->scope=$shop;
        $this->initialisationFromShop($shop, $request);

        return $this->handle($webpage);
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function inFulfilment(Organisation $organisation, Fulfilment $fulfilment, Website $website, Webpage $webpage, ActionRequest $request): Webpage
    {
        $this->scope=$fulfilment;
        $this->initialisationFromFulfilment($fulfilment, $request);

        return $this->handle($webpage);
    }

    /**
     * @throws Exception
     */
    public function htmlResponse(Webpage $webpage, ActionRequest $request): Response
    {
        return Inertia::render(
            'EditModel',
            [
                'title'       => __("Webpage's settings"),
                'breadcrumbs' => $this->getBreadcrumbs($request->route()->getName(), $request->route()->originalParameters()),

                'pageHead' => [
                    'title' => __('webpage settings'),


                    'iconRight' =>
                        [
                            'icon'  => ['fal', 'sliders-h'],
                            'title' => __("Webpage settings")
                        ],

                    'actions' => [
                        [
                            'type'  => 'button',
                            'style' => 'exit',
                            'label' => __('Exit settings'),
                            'route' => [
                                'name'       => preg_replace('/edit$/', 'show', $request->route()->getName()),
                                'parameters' => array_values($request->route()->originalParameters())
                            ]
                        ]
                    ],
                ],
                'formData' => [
                    'blueprint' => [
                        [
                            'label'  => __('SEO (Google Search)'),
                            'icon'   => 'fab fa-google',
                            'fields' => [
                                'google_search' => [
                                    'type'     => 'googleSearch',
                                    'value'    => [
                                        'seotitle'       => Arr::get($webpage->data, 'seotitle')       ?? '',
                                        'seodescription' => Arr::get($webpage->data, 'seodescription') ?? '',
                                        'seourl'         => Arr::get($webpage->data, 'seourl')         ?? '',
                                    ],
                                    'noTitle'  => true,
                                ]
                            ]
                        ],
                    ],
                    'args'      => [
                        'updateRoute' => [
                            'name'       => 'grp.models.shop.webpage.update',
                            'parameters' => [
                                'shop'    => $webpage->website->shop->id,
                                'webpage' => $webpage->id
                            ]
                        ],
                    ]
                ],

            ]
        );
    }


    public function getBreadcrumbs(string $routeName, array $routeParameters): array
    {
        return ShowWebpage::make()->getBreadcrumbs(
            $routeName,
            $routeParameters,
            suffix: '('.__('settings').')'
        );
    }


}

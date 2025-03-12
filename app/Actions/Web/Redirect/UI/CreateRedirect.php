<?php
/*
 * author Arya Permana - Kirin
 * created on 12-03-2025-11h-57m
 * github: https://github.com/KirinZero0
 * copyright 2025
*/

namespace App\Actions\Web\Redirect\UI;

use App\Actions\OrgAction;
use App\Actions\Traits\Authorisations\WithFulfilmentShopEditAuthorisation;
use App\Actions\Web\Webpage\UI\ShowWebpage;
use App\Enums\Web\Redirect\RedirectTypeEnum;
use App\Models\Catalogue\Shop;
use App\Models\Fulfilment\Fulfilment;
use App\Models\SysAdmin\Organisation;
use App\Models\Web\Webpage;
use App\Models\Web\Website;
use Exception;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Spatie\LaravelOptions\Options;

class CreateRedirect extends OrgAction
{
    /**
     * @throws Exception
     */
    public function handle(Website|Webpage $parent, ActionRequest $request): Response
    {
        $route = [];
        if($parent instanceof Website)
        {
            $route = [
                'name'       => 'grp.models.website.redirect.store',
                'parameters' => [
                    'website' => $parent->id,
                ]
            ];
        } elseif ($parent instanceof Webpage) {
            $route = [
                'name' => 'grp.models.webpage.redirect.store',
                'parameters' => [
                    'webpage' => $parent->id
                ]
            ];
        }
        return Inertia::render(
            'CreateModel',
            [
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->getName(),
                    $request->route()->originalParameters()
                ),
                'title'       => __('New Redirect'),
                'pageHead'    => [
                    'title' => __('New Redirect')
                ],
                'formData'    => [
                    'fullLayout' => true,
                    'blueprint'  =>
                        [
                            [
                                'title'  => __('New Redirect'),
                                'fields' => [
                                    'type' => [
                                        'type'     => 'select',
                                        'label'    => __('type'),
                                        'required' => true,
                                        'options'  => Options::forEnum(RedirectTypeEnum::class),
                                        'required' => true,
                                    ],
                                    'url'  => [
                                        'type'     => 'input',
                                        'label'    => __('url'),
                                        'required' => true
                                    ],
                                    'path' => [
                                        'type'     => 'input',
                                        'label'    => __('path'),
                                        'required' => true
                                    ],
                                ]
                            ]
                        ],
                    'route'      => $route
                ],

            ]
        );
    }

    /**
     * @throws Exception
     */
    public function asController(Organisation $organisation, Shop $shop, Website $website, ActionRequest $request): Response
    {
        $this->initialisationFromShop($shop, $request);

        return $this->handle($website, $request);
    }

    public function inWebsiteInFulfilment(Organisation $organisation, Fulfilment $fulfilment, Website $website, ActionRequest $request): Response
    {
        $this->initialisationFromFulfilment($fulfilment, $request);

        return $this->handle($website, $request);
    }

    public function inWebpage(Organisation $organisation, Shop $shop, Website $website, Webpage $webpage, ActionRequest $request): Response
    {
        $this->initialisationFromShop($shop, $request);

        return $this->handle($webpage, $request);
    }

    public function inWebpageInFulfilment(Organisation $organisation, Fulfilment $fulfilment, Website $website, Webpage $webpage, ActionRequest $request): Response
    {
        $this->initialisationFromFulfilment($fulfilment, $request);

        return $this->handle($webpage, $request);
    }

    public function getBreadcrumbs(string $routeName, array $routeParameters): array
    {
        return array_merge(
            ShowWebpage::make()->getBreadcrumbs(
                routeName: $routeName,
                routeParameters: $routeParameters,
            ),
            [
                [
                    'type'          => 'creatingModel',
                    'creatingModel' => [
                        'label' => __('Creating Redirect'),
                    ]
                ]
            ]
        );
    }

}

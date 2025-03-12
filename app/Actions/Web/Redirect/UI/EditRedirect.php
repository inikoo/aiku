<?php

/*
 * author Arya Permana - Kirin
 * created on 12-03-2025-13h-11m
 * github: https://github.com/KirinZero0
 * copyright 2025
*/

namespace App\Actions\Web\Redirect\UI;

use App\Actions\OrgAction;
use App\Enums\Web\Redirect\RedirectTypeEnum;
use App\Models\Catalogue\Shop;
use App\Models\Fulfilment\Fulfilment;
use App\Models\SysAdmin\Organisation;
use App\Models\Web\Redirect;
use App\Models\Web\Webpage;
use App\Models\Web\Website;
use Exception;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Spatie\LaravelOptions\Options;

class EditRedirect extends OrgAction
{
    /**
     * @throws Exception
     */
    public function handle(Redirect $redirect, ActionRequest $request): Response
    {
        return Inertia::render(
            'CreateModel',
            [
                // 'breadcrumbs' => $this->getBreadcrumbs(
                //     $request->route()->originalParameters()
                // ),
                'title'       => __('Edit Redirect'),
                'pageHead'    => [
                    'title' => __('Edit Redirect')
                ],
                'formData'    => [
                    'fullLayout' => true,
                    'blueprint'  =>
                        [
                            [
                                'title'  => __('Edit Redirect'),
                                'fields' => [
                                    'type' => [
                                        'type'     => 'select',
                                        'label'    => __('type'),
                                        'options'  => Options::forEnum(RedirectTypeEnum::class),
                                        'value'    => $redirect->type
                                    ],
                                    'url'  => [
                                        'type'     => 'input',
                                        'label'    => __('url'),
                                        'value'    => $redirect->url
                                    ],
                                    'path' => [
                                        'type'     => 'input',
                                        'label'    => __('path'),
                                        'value'    => $redirect->path
                                    ],
                                ]
                            ]
                        ],
                    'route'      => [
                        'name' => 'grp.models.redirect.update',
                        'parameters' => [
                            'redirect' => $redirect->id
                        ]
                    ]
                ],

            ]
        );
    }

    /**
     * @throws Exception
     */
    public function inWebpage(Organisation $organisation, Shop $shop, Website $website, Webpage $webpage, Redirect $redirect, ActionRequest $request): Response
    {
        $this->initialisationFromShop($shop, $request);

        return $this->handle($redirect, $request);
    }

    public function inWebpageInFulfilment(Organisation $organisation, Fulfilment $fulfilment, Website $website, Webpage $webpage, Redirect $redirect, ActionRequest $request): Response
    {
        $this->initialisationFromFulfilment($fulfilment, $request);

        return $this->handle($redirect, $request);
    }

    // public function getBreadcrumbs(string $routeName, array $routeParameters): array
    // {
    //     return ShowInvoice::make()->getBreadcrumbs(
    //         routeName: preg_replace('/edit$/', 'show', $routeName),
    //         routeParameters: $routeParameters,
    //         suffix: '('.__('Editing').')'
    //     );
    // }

}

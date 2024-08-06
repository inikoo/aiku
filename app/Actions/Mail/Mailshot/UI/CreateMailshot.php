<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 27 Apr 2024 08:53:02 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Mail\Mailshot\UI;

use App\Actions\OrgAction;
use App\Models\Catalogue\Shop;
use App\Models\Mail\Outbox;
use App\Models\SysAdmin\Organisation;
use Exception;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class CreateMailshot extends OrgAction
{
    /**
     * @throws Exception
     */
    public function handle(Shop|Outbox $parent, ActionRequest $request): Response
    {
        return Inertia::render(
            'CreateModel',
            [
                'breadcrumbs' => $this->getBreadcrumbs(
                    $parent->organisation,
                    $request->route()->getName(),
                    $request->route()->originalParameters()
                ),
                'title'    => __('new mailshot'),
                'pageHead' => [
                    'title' => __('new mailshot')
                ],
                'formData' => [
                    'fullLayout' => true,
                    'blueprint'  =>
                        [
                            [
                                'title'  => __('name'),
                                'fields' => [
                                    'email_template_id' => [
                                        'type'       => 'input',
                                        'label'      => __('price'),
                                        'required'   => true
                                    ],
                                ]
                            ]
                        ],
                    'route' => [
                        'name'       => 'grp.models.org.fulfilment.rentals.store',
                        'parameters' => [
                            'organisation' => $parent->organisation_id,
                            'shop'         => $parent->id,
                        ]
                    ]
                ],

            ]
        );
    }

    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->hasPermissionTo("crm.{$this->shop->id}.edit");
    }

    /**
     * @throws Exception
     */
    public function asController(Organisation $organisation, Shop $shop, ActionRequest $request): Response
    {
        $this->initialisationFromShop($shop, $request);

        return $this->handle($shop, $request);
    }

    public function getBreadcrumbs(Organisation $parent, string $routeName, array $routeParameters): array
    {
        return array_merge(
            IndexMailshots::make()->getBreadcrumbs(
                routeName: $routeName,
                routeParameters: $routeParameters,
                parent: $parent
            ),
            [
                [
                    'type'          => 'creatingModel',
                    'creatingModel' => [
                        'label' => __('Creating mailshot'),
                    ]
                ]
            ]
        );
    }

}

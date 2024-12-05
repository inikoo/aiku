<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 19 Nov 2024 11:09:35 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Comms\Mailshot\UI;

use App\Actions\CRM\Prospect\Queries\UI\IndexProspectQueries;
use App\Actions\OrgAction;
use App\Models\Catalogue\Shop;
use App\Models\Comms\Outbox;
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
        $fields[] = [
            'title'  => '',
            'fields' => [
                'subject' => [
                    'type'        => 'input',
                    'label'       => __('subject'),
                    'placeholder' => __('Email subject'),
                    'required'    => true,
                    'value'       => '',
                ],
            ]
        ];

        $tags = explode(',', $request->get('tags'));

        $fields[] = [
            'title'  => '',
            'fields' => [
                'recipients_recipe' => [
                    'type'        => 'prospectRecipients',
                    'label'       => __('recipients'),
                    'required'    => true,
                    'options'     => [
                        'query'                  => IndexProspectQueries::run(),
                        'custom_prospects_query' => '',
                    ],
                    'full'      => true,
                    'value'     => [
                        'recipient_builder_type' => 'query',
                        'recipient_builder_data' => [
                            'query'                     => null,
                            'custom_prospects_query'    => $tags[0] != '' ? [
                                'tags'   => [
                                    'logic'    => 'all',
                                    'tag_ids'  => $tags
                                ],
                            ] : null,
                            'prospects' => null,
                        ]
                    ]
                ],
            ]
        ];

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
                                'fields' => $fields
                            ]
                        ],
                    'route' => [
                        'name'       => 'grp.models.shop.mailshot.store',
                        'parameters' => [
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

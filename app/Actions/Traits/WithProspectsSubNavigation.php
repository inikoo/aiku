<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 15 Nov 2023 17:42:53 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Traits;

use Lorisleiva\Actions\ActionRequest;

trait WithProspectsSubNavigation
{
    public function getSubNavigation(ActionRequest $request): array
    {
        $meta = [];

        $meta[] = [
            'href'     => [
                'name'       => 'grp.org.shops.show.crm.prospects.index',
                'parameters' => array_merge(
                    $request->route()->originalParameters(),
                    [
                        '_query' => [
                            'tab' => 'prospects'
                        ]
                    ]
                )
            ],
            'number'   => $this->parent->crmStats->number_prospects,
            'label'    => __('Prospects'),
            'leftIcon' => [
                'icon'    => 'fal fa-transporter',
                'tooltip' => __('prospects')
            ]
        ];

        if ($this->parent->crmStats->number_prospects > 0) {
            $meta[] = [
                'href'     => [
                    'name'       => 'grp.org.shops.show.crm.prospects.mailshots.index',
                    'parameters' => $request->route()->originalParameters()
                ],
                'number'   => $this->parent->mailStats->number_mailshots_type_prospect_mailshot,
                'label'    => __('Mailshots'),
                'leftIcon' => [
                    'icon'    => 'fal fa-mail-bulk',
                    'tooltip' => __('mailshots')
                ]
            ];
        }

        $meta[] = [
            // 'href'     => [
            //     'name'       => 'grp.org.shops.show.crm.prospects.lists.index',
            //     'parameters' => $request->route()->originalParameters()
            // ],
            'number'   => $this->parent->crmStats->number_prospect_queries,
            'label'    => __('Lists'),
            'leftIcon' => [
                'icon'    => 'fal fa-code-branch',
                'tooltip' => __('lists')
            ]
        ];

        $meta[] = [
            // 'href'     => [
            //     'name'       => 'grp.org.shops.show.crm.prospects.tags.index',
            //     'parameters' => $request->route()->originalParameters()
            // ],
            'number'   => $this->parent->crmStats->number_tags,
            'label'    => __('Tags'),
            'leftIcon' => [
                'icon'    => 'fal fa-tags',
                'tooltip' => __('tags')
            ]
        ];

        return $meta;
    }
}

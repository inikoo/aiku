<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 20 Jun 2023 20:32:25 Malaysia Time, Pantai Lembeng, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\RecurringBill\UI;

use App\Actions\Fulfilment\Setting\ShowFulfilmentSettingDashboard;
use App\Actions\OrgAction;
use App\Models\Fulfilment\Fulfilment;
use App\Models\SysAdmin\Organisation;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class ShowRecurringBillSetting extends OrgAction
{
    public function handle(Fulfilment $fulfilment): Fulfilment
    {
        return $fulfilment;
    }

    public function authorize(ActionRequest $request): bool
    {
        $this->canEdit = $request->user()->hasPermissionTo("fulfilment-shop.{$this->fulfilment->id}.edit");

        return $request->user()->hasPermissionTo("fulfilment-shop.{$this->fulfilment->id}.view");
    }


    public function asController(Organisation $organisation, Fulfilment $fulfilment, ActionRequest $request): Fulfilment
    {
        $this->initialisationFromFulfilment($fulfilment, $request);

        return $this->handle($fulfilment);
    }

    public function htmlResponse(Fulfilment $fulfilment, ActionRequest $request): Response
    {
        return Inertia::render(
            'EditModel',
            [
                'title'       => __('fulfilment setting'),
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->getName(),
                    $request->route()->originalParameters()
                ),
                'pageHead'    => [
                    'title'    => $fulfilment->shop->name,
                ],

                'formData' => [
                    'blueprint' => [
                        [
                            'title'  => __('recurring bill settings'),
                            'label'  => __('cut off day'),
                            'fields' => [

                                'monthly_cut_off_day' => [
                                    'type'  => 'date',
                                    'label' => __('monthly cut off day'),
                                    'value' => ''
                                ],
                                'weekly_cut_off_day' => [
                                    'type'      => 'select',
                                    'options'   => [
                                        [
                                            'label' => __('Monday'),
                                            'value' => 'monday'
                                        ],
                                        [
                                            'label' => __('Tuesday'),
                                            'value' => 'tuesday'
                                        ],
                                        [
                                            'label' => __('Wednesday'),
                                            'value' => 'wednesday'
                                        ],
                                        [
                                            'label' => __('Thursday'),
                                            'value' => 'thursday'
                                        ],
                                        [
                                            'label' => __('Friday'),
                                            'value' => 'friday'
                                        ],
                                    ],
                                    'required'  => true,
                                    'label'     => __('weekly cut off day'),
                                    'value'     => ''
                                ],
                            ]
                        ]

                    ],
                    'args'      => [
                        'updateRoute' => [
                            'name'       => 'grp.models.org.fulfilment.settings.recurring-bill',
                            'parameters' => [
                                'organisation' => $fulfilment->organisation_id,
                                'fulfilment' => $fulfilment->id
                                ]
                        ],
                    ]

                ],

            ]
        );
    }


    public function getBreadcrumbs(string $routeName, array $routeParameters): array
    {
        return ShowFulfilmentSettingDashboard::make()->getBreadcrumbs(
            'grp.org.fulfilments.show.setting.dashboard',
            $routeParameters
        );
    }
}

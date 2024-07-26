<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 25 May 2023 21:14:38 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\StoredItemAudit\UI;

use App\Actions\Fulfilment\Fulfilment\UI\ShowFulfilment;
use App\Actions\OrgAction;
use App\Models\Fulfilment\Fulfilment;
use App\Models\Fulfilment\FulfilmentCustomer;
use App\Models\SysAdmin\Organisation;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class CreateStoredItemAudit extends OrgAction
{
    public function authorize(ActionRequest $request): bool
    {
        $this->canEdit = $request->user()->hasPermissionTo("fulfilment-shop.{$this->fulfilment->id}.edit");

        return
            (
                $request->user()->tokenCan('root') or
                $request->user()->hasPermissionTo("human-resources.{$this->organisation->id}.view")
            );
    }

    public function htmlResponse(ActionRequest $request): Response
    {
        return Inertia::render(
            'CreateModel',
            [
                'breadcrumbs' => $this->getBreadcrumbs($request->route()->originalParameters()),
                'title'       => __('stored item audit'),
                'pageHead'    => [
                    'title'  => __('stored item audit'),
                ],
                'formData' => [
                    'blueprint' => [
                        [
                            'title'  => __('Audit'),
                            'icon'   => ['fal', 'fa-narwhal'],
                            'fields' => [
                                'reference' => [
                                    'type'    => 'input',
                                    'label'   => __('reference'),
                                    'value'   => '',
                                    'required'=> true
                                ],
                            ]
                        ]
                    ],
                    'route' => [
                        'name'       => 'grp.models.fulfilment-customer.stored_item_audits.store',
                        'parameters' => [
                            'fulfilmentCustomer' => request()->route('fulfilmentCustomer')
                        ]
                    ]
                ],
            ]
        );
    }

    public function asController(Organisation $organisation, ActionRequest $request): ActionRequest
    {
        $this->initialisation($organisation, $request);

        return $request;
    }

    public function inFulfilment(Organisation $organisation, Fulfilment $fulfilment, ActionRequest $request): ActionRequest
    {
        $this->initialisationFromFulfilment($fulfilment, $request);

        return $request;
    }

    public function inFulfilmentCustomer(Organisation $organisation, Fulfilment $fulfilment, FulfilmentCustomer $fulfilmentCustomer, ActionRequest $request): ActionRequest
    {
        $this->initialisationFromFulfilment($fulfilment, $request);

        return $request;
    }

    public function getBreadcrumbs(array $routeParameters): array
    {
        return array_merge(
            ShowFulfilment::make()->getBreadcrumbs($routeParameters),
            [
                [
                    'type'   => 'simple',
                    'simple' => [
                        'route' => [
                            'name'       => 'grp.org.fulfilments.show.operations.stored-item-audits.index',
                            'parameters' => $routeParameters
                        ],
                        'label' => __('stored item audit'),
                        'icon'  => 'fal fa-bars',
                    ],
                ]
            ]
        );
    }
}

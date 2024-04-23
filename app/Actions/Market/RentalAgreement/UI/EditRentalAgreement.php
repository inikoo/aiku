<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Mon, 13 Mar 2023 15:34:29 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Market\RentalAgreement\UI;

use App\Actions\Fulfilment\FulfilmentCustomer\ShowFulfilmentCustomer;
use App\Actions\OrgAction;
use App\Models\Fulfilment\Fulfilment;
use App\Models\Fulfilment\FulfilmentCustomer;
use App\Models\Market\RentalAgreement;
use App\Models\SysAdmin\Organisation;
use Exception;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class EditRentalAgreement extends OrgAction
{
    /**
     * @throws Exception
     */
    public function handle(RentalAgreement $rentalAgreement, ActionRequest $request): Response
    {
        return Inertia::render(
            'EditModel',
            [
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->originalParameters()
                ),
                'title'    => __('new rental agreement'),
                'pageHead' => [
                    'title' => __('new rental agreement')
                ],
                'formData' => [
                    'fullLayout' => true,
                    'blueprint'  =>
                        [
                            [
                                'title'  => __('name'),
                                'fields' => [
                                    'billing_cycle' => [
                                        'type'     => 'input',
                                        'label'    => __('billing cycle'),
                                        'required' => true,
                                        'value'    => $rentalAgreement->billing_cycle
                                    ],
                                    'pallets_limit' => [
                                        'type'     => 'input',
                                        'label'    => __('pallets limit'),
                                        'required' => false,
                                        'value'    => $rentalAgreement->pallets_limit
                                    ],
                                    'rental' => [
                                        'type'             => 'rental',
                                        'label'            => __('Rental'),
                                        'required'         => true,
                                        'full'             => true,
                                        'indexRentalRoute' => [
                                            'name'       => 'grp.org.fulfilments.show.products.rentals.index',
                                            'parameters' => [
                                                'organisation' => $this->organisation->slug,
                                                'fulfilment'   => $rentalAgreement->fulfilment->slug
                                            ]
                                        ],
                                        'value' => ''
                                    ],
                                ]
                            ]
                        ],
                    'route' => [
                        'name'       => 'grp.models.fulfilment-customer.rental-agreements.update',
                        'parameters' => [
                            'rentalAgreement' => $rentalAgreement->id,
                        ]
                    ]
                ],
            ]
        );
    }

    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->hasPermissionTo("fulfilments.{$this->fulfilment->id}.edit");
    }

    /**
     * @throws Exception
     */
    public function asController(Organisation $organisation, Fulfilment $fulfilment, FulfilmentCustomer $fulfilmentCustomer, RentalAgreement $rentalAgreement, ActionRequest $request): Response
    {
        $this->initialisationFromFulfilment($fulfilment, $request);

        return $this->handle($rentalAgreement, $request);
    }

    public function getBreadcrumbs(array $routeParameters): array
    {
        return array_merge(
            ShowFulfilmentCustomer::make()->getBreadcrumbs(
                routeParameters: $routeParameters,
            ),
            [
                [
                    'type'          => 'creatingModel',
                    'creatingModel' => [
                        'label' => __('creating rental agreement'),
                    ]
                ]
            ]
        );
    }

}

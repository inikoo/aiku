<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 27 Apr 2024 08:53:02 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\RentalAgreement\UI;

use App\Actions\Fulfilment\FulfilmentCustomer\ShowFulfilmentCustomer;
use App\Actions\OrgAction;
use App\Enums\Fulfilment\RentalAgreement\RentalAgreementBillingCycleEnum;
use App\Models\Fulfilment\Fulfilment;
use App\Models\Fulfilment\FulfilmentCustomer;
use App\Models\SysAdmin\Organisation;
use Exception;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Spatie\LaravelOptions\Options;

class CreateRentalAgreement extends OrgAction
{
    /**
     * @throws Exception
     */
    public function handle(FulfilmentCustomer $fulfilmentCustomer, ActionRequest $request): Response
    {
        return Inertia::render(
            'CreateModel',
            [
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->originalParameters()
                ),
                'title'    => __('new rental agreement'),
                'pageHead' => [
                    'title' => __('new rental agreement')
                ],
                'formData' => [
                    'fullLayout'    => true,
                    'submitButton'  => 'dropdown',
                    'submitField'   => 'state',
                    'blueprint'     =>
                        [
                            [
                                'title'  => __(''),
                                'fields' => [
                                    'billing_cycle' => [
                                        'type'       => 'select',
                                        'label'      => __('billing cycle'),
                                        'required'   => true,
                                        'options'    => Options::forEnum(RentalAgreementBillingCycleEnum::class),
                                        'value'      => RentalAgreementBillingCycleEnum::MONTHLY->value
                                    ],
                                    'pallets_limit' => [
                                        'type'        => 'input',
                                        'label'       => __('pallets limit'),
                                        'placeholder' => '0',
                                        'required'    => false
                                    ],
                                    'rental' => [
                                        'type'             => 'rental',
                                        'label'            => __('Rental'),
                                        'required'         => false,
                                        'full'             => true,
                                        'indexRentalRoute' => [
                                            'name'       => 'grp.org.fulfilments.show.products.rentals.index',
                                            'parameters' => [
                                                'organisation' => $this->organisation->slug,
                                                'fulfilment'   => $fulfilmentCustomer->fulfilment->slug
                                            ]
                                        ]
                                    ],
                                ]
                            ]
                        ],
                    'route' => [
                       [
                        'label'      => __('Save'),
                        'key'        => 'active',
                        'name'       => 'grp.models.fulfilment-customer.rental-agreements.store',
                        'parameters' => ['fulfilmentCustomer' => $fulfilmentCustomer->id]
                       ],
                       [
                        'label'      => __('Draft'),
                        'key'        => 'draft',
                        'name'       => 'grp.models.fulfilment-customer.rental-agreements.store',
                        'parameters' => ['fulfilmentCustomer' => $fulfilmentCustomer->id]
                       ],

                    ]
                ],

            ]
        );
    }

    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->hasPermissionTo("fulfilment-shop.{$this->fulfilment->id}.edit");
    }

    /**
     * @throws Exception
     */
    public function asController(Organisation $organisation, Fulfilment $fulfilment, FulfilmentCustomer $fulfilmentCustomer, ActionRequest $request): Response
    {
        $this->initialisationFromFulfilment($fulfilment, $request);

        return $this->handle($fulfilmentCustomer, $request);
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

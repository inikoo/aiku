<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Tue, 14 Mar 2023 09:31:03 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Inventory\StockFamily\UI;

use App\Actions\InertiaAction;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class CreateStockFamily extends InertiaAction
{
    use HasUIStockFamilies;


    public function handle(): Response
    {
        return Inertia::render(
            'CreateModel',
            [
                'breadcrumbs' => $this->getBreadcrumbs(),
                'title'       => __('new stock family'),
                'pageHead'    => [
                    'title'        => __('new stock family'),
                    'cancelCreate' => [
                        'route' => [
                            'name'       => 'inventory.stock-families.index',
                            'parameters' => array_values($this->originalParameters)
                        ],
                    ]

                ],
                'formData' => [
                    'blueprint' => [
                        [
                            'title'  => __('status/Id'),
                            'fields' => [

                                'status' => [
                                    'type'  => 'input',
                                    'label' => __('status'),
                                    'value' => ''
                                ],
                                'reference' => [
                                    'type'  => 'input',
                                    'label' => __('reference'),
                                    'value' => ''
                                ],
                                'part_symbol' => [
                                    'type'  => 'input',
                                    'label' => __('part symbol'),
                                    'value' => ''
                                ],
                                'family' => [
                                    'type'  => 'input',
                                    'label' => __('family'),
                                    'value' => ''
                                ],
                            ]
                        ],
                        [
                            'title'  => __('cost/pricing'),
                            'icon'   => 'fa-light fa-phone',
                            'fields' => [
                                'unit_cost' => [
                                    'type'    => 'input',
                                    'label'   => __('unit cost'),
                                    'value'   => '',
                                ],
                                'unit_expense' => [
                                    'type'    => 'input',
                                    'label'   => __('unit expense'),
                                    'value'   => '',
                                ],
                                'percentage_extra_costs' => [
                                    'type'    => 'input',
                                    'label'   => __('percentage extra cost'),
                                    'value'   => '',
                                ],
                                'unit_recommended_price' => [
                                    'type'    => 'input',
                                    'label'   => __('unit recommended price'),
                                    'value'   => '',
                                ],
                                'unit_recommended_rpp' => [
                                    'type'    => 'input',
                                    'label'   => __('unit recommended rpp'),
                                    'value'   => '',
                                ],

                            ]
                        ],
                        [
                            'title'  => __('unit'),
                            'fields' => [

                                'unit_description' => [
                                    'type'  => 'input',
                                    'label' => __('unit description'),
                                    'value' => ''
                                ],
                                'unit_barcode' => [
                                    'type'  => 'input',
                                    'label' => __('unit barcode (EAN-13)'),
                                    'value' => ''
                                ],
                                'unit_label' => [
                                    'type'  => 'input',
                                    'label' => __('unit label'),
                                    'value' => ''
                                ],
                                'weight_shown_in_website' => [
                                    'type'  => 'input',
                                    'label' => __('weight show in website'),
                                    'value' => ''
                                ],
                                'dimensions_shown_in_website' => [
                                    'type'  => 'input',
                                    'label' => __('dimensions shown in website'),
                                    'value' => ''
                                ],
                            ]
                        ],
                        [
                            'title'  => __("stock keeping outer (SKO)"),
                            'fields' => [

                                'units_per_sko' => [
                                    'type'  => 'input',
                                    'label' => __('units per sko'),
                                    'value' => ''
                                ],
                                'sko_per_selling_outer' => [
                                    'type'  => 'input',
                                    'label' => __('sko per selling outer (recommended)'),
                                    'value' => ''
                                ],
                                'sko_barcode' => [
                                    'type'  => 'input',
                                    'label' => __('sko barcode (stock control)'),
                                    'value' => ''
                                ],
                                'sko_description' => [
                                    'type'  => 'input',
                                    'label' => __('sko description (for picking aid)'),
                                    'value' => ''
                                ],
                                'sko_description_note' => [
                                    'type'  => 'input',
                                    'label' => __('sko description note'),
                                    'value' => ''
                                ],
                                'sko_weight' => [
                                    'type'  => 'input',
                                    'label' => __('sko weight'),
                                    'value' => ''
                                ],
                                'sko_dimensions' => [
                                    'type'  => 'input',
                                    'label' => __('sko dimensions'),
                                    'value' => ''
                                ],
                            ]
                        ],
                        [
                            'title'  => __('properties'),
                            'fields' => [
                                'cpnp_number' => [
                                    'type'  => 'input',
                                    'label' => __('cpnp number'),
                                    'value' => ''
                                ],
                                'ufi' => [
                                    'type'  => 'input',
                                    'label' => __('ufi (poisons centres)'),
                                    'value' => ''
                                ],
                                'materials_ingredients' => [
                                    'type'  => 'input',
                                    'label' => __('materials/ingredients'),
                                    'value' => ''
                                ],
                                'country_of_origin' => [
                                    'type'  => 'input',
                                    'label' => __('country of origin'),
                                    'value' => ''
                                ],
                                'tariff_code' => [
                                    'type'  => 'input',
                                    'label' => __('tariff code'),
                                    'value' => ''
                                ],
                                'duty_rate' => [
                                    'type'  => 'input',
                                    'label' => __('duty rate'),
                                    'value' => ''
                                ],
                                'hts_us' => [
                                    'type'  => 'input',
                                    'label' => __('hts us'),
                                    'value' => ''
                                ],
                            ]
                        ],
                        [
                            'title'  => __('health & safety'),
                            'fields' => [

                                'un_number' => [
                                    'type'  => 'input',
                                    'label' => __('un number'),
                                    'value' => ''
                                ],
                                'un_class' => [
                                    'type'  => 'input',
                                    'label' => __('un class'),
                                    'value' => ''
                                ],
                                'picking_group' => [
                                    'type'  => 'input',
                                    'label' => __('picking group'),
                                    'value' => ''
                                ],
                                'proper_shipping_name' => [
                                    'type'  => 'input',
                                    'label' => __('proper shipping name'),
                                    'value' => ''
                                ],
                                'hazard_identification_number' => [
                                    'type'  => 'input',
                                    'label' => __('hazard identification number'),
                                    'value' => ''
                                ],
                            ]
                        ],
                        [
                            'title'  => __('operations'),
                            'fields' => [

                                'set_raw_materials' => [
                                    'type'  => 'input',
                                    'label' => __('set as raw materials'),
                                    'value' => ''
                                ],
                            ]
                        ],

                    ],
                    'route' => [
                        'name' => 'models.stock-family.store',
                    ]
                ],

            ]
        );
    }

    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->can('inventory.stocks.edit');
    }


    public function asController(ActionRequest $request): Response
    {
        $this->initialisation($request);

        return $this->handle();
    }
}

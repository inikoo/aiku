<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Tue, 14 Mar 2023 09:31:03 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Procurement\Supplier\UI;

use App\Actions\InertiaAction;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class CreateSupplier extends InertiaAction
{
    use HasUISuppliers;


    public function handle(): Response
    {
        return Inertia::render(
            'CreateModel',
            [
                'breadcrumbs' => $this->getBreadcrumbs(),
                'title'       => __('new supplier'),
                'pageHead'    => [
                    'title'        => __('new supplier'),
                    'cancelCreate' => [
                        'route' => [
                            'name'       => 'procurement.suppliers.index',
                            'parameters' => array_values($this->originalParameters)
                        ],
                    ]

                ],


            ]
        );
    }

    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->can('procurement.suppliers.edit');
    }


    public function asController(ActionRequest $request): Response
    {
        $this->initialisation($request);

        return $this->handle();
    }
}

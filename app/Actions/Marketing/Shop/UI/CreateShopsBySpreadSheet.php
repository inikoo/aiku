<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Thu, 18 May 2023 14:27:33 Central European Summer, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Marketing\Shop\UI;

use App\Actions\InertiaAction;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

/**
 * @property array $breadcrumbs
 * @property bool $canEdit
 * @property string $title
 */
class CreateShopsBySpreadSheet extends InertiaAction
{
    public function authorize(ActionRequest $request): bool
    {
        $this->canEdit = $request->user()->can('shops');

        return
            (
                $request->user()->tokenCan('root') or
                $request->user()->hasPermissionTo('shops.edit')
            );
    }

    public function asController(ActionRequest $request)
    {
        return $request;
    }


    public function htmlResponse(ActionRequest $request): Response
    {
        return Inertia::render(
            'CreateModelBySpreadSheet',
            [
                'title'     => __('shops'),
                'pageHead'  => [
                    'title' => __('Upload shops'),
                ],
                'sheetData' => [

                    'columns' => [
                        [
                            'id'       => 'code',
                            'name'     => __('Code'),
                            'type'     => 'string',
                            'prop'     => 'code',
                            'required' => true,
                        ],
                        [
                            'id'       => 'name',
                            'name'     => __('Label'),
                            'type'     => 'string',
                            'prop'     => 'name',
                            'required' => true,
                        ],
                    ],
                ],
                'saveRoute' => [
                    'name' => 'models.shop.store-multi',
                ]

            ]
        );
    }


}

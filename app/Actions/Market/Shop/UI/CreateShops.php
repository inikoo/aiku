<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Thu, 18 May 2023 14:27:33 Central European Summer, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Market\Shop\UI;

use App\Actions\InertiaAction;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

/**
 * @property array $breadcrumbs
 * @property bool $canEdit
 * @property string $title
 */
class CreateShops extends InertiaAction
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

    public function asController(ActionRequest $request): ActionRequest
    {
        return $request;
    }


    public function htmlResponse(ActionRequest $request): Response
    {

        return Inertia::render(
            'CreateModelBySpreadSheet',
            [
                'title'     => __('shops'),
                'documentName'     => 'tes',
                'pageHead'  => [
                    'title' => __('Upload shops'),
                    'exitEdit' => [
                        'label' => __('Back'),
                        'route' => [
                            'name'       => 'shops.index',
                            'parameters' => array_values($this->originalParameters)
                        ],
                    ],
                    'clearMulti' => [
                        'route' => [
                            'name'       => 'shops.create-multi-clear',
                            'parameters' => array_values($this->originalParameters)
                        ],
                    ]
                ],
                'sheetData' => [
                    'columns' => [
                        [
                            'id'             => 'code',
                            'name'           => __('Code'),
                            'columnType'     => 'string',
                            'prop'           => 'code',
                            'required'       => true,
                        ],
                        [
                            'id'             => 'name',
                            'name'           => __('Label'),
                            'columnType'     => 'string',
                            'prop'           => 'name',
                            'required'       => true,
                        ],
                        [
                            'id'             => 'hidden',
                            'name'           => __('hidden'),
                            'columnType'     => 'string',
                            'prop'           => 'hidden',
                            'required'       => true,
                            'hidden'         => true
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

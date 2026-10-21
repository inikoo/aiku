<?php

/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Wed, 21 Oct 2026, Bali, Indonesia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Retina\Fulfilment\Leaflet\UI;

use App\Actions\Retina\Fulfilment\Leaflet\WithRetinaFulfilmentInserts;
use App\Actions\Retina\UI\SysAdmin\ShowRetinaFulfilmentSysAdminDashboard;
use App\Actions\RetinaAction;
use App\Enums\Catalogue\Leaflet\LeafletStateEnum;
use App\Enums\Catalogue\Leaflet\LeafletTypeEnum;
use App\Models\Billables\ModelHasLeaflet;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class ShowRetinaFulfilmentInserts extends RetinaAction
{
    use WithRetinaFulfilmentInserts;

    public function asController(ActionRequest $request): Response
    {
        $this->initialisation($request);

        return $this->handle();
    }

    public function handle(): Response
    {
        $title = __('Inserts');

        return Inertia::render(
            'SysAdmin/RetinaFulfilmentInserts',
            [
                'breadcrumbs' => $this->getBreadcrumbs(),
                'title'       => $title,
                'pageHead'    => [
                    'title' => $title,
                    'icon'  => [
                        'icon'  => ['fal', 'fa-gift'],
                        'title' => $title
                    ],
                ],
                'inserts'     => $this->getInserts(),
                'typeOptions' => collect(LeafletTypeEnum::labels())
                    ->except(LeafletTypeEnum::PERSONALISED_MESSAGE->value)
                    ->map(fn (string $label, string $code) => ['name' => $label, 'code' => $code])
                    ->values()
                    ->all(),
                'storeRoute'    => ['name' => 'retina.sysadmin.inserts.store'],
                'updateRoute'   => ['name' => 'retina.sysadmin.inserts.update'],
                'deleteRoute'   => ['name' => 'retina.sysadmin.inserts.delete'],
                'downloadRoute' => ['name' => 'retina.sysadmin.packaging-preferences.leaflet.download'],
            ]
        );
    }

    /**
     * @return array<int, array{id: int, name: string, type: string, type_label: string, file_name: string|null, mime_type: string|null, size: string|null, uploaded_at: string|null, included: bool}>
     */
    private function getInserts(): array
    {
        return ModelHasLeaflet::where('model_type', 'Customer')
            ->where('model_id', $this->customer->id)
            ->where('shop_id', $this->shop->id)
            ->whereNotNull('media_id')
            ->with('media')
            ->orderBy('name')
            ->get()
            ->map(fn (ModelHasLeaflet $customerLeaflet) => [
                'id'          => $customerLeaflet->id,
                'name'        => $customerLeaflet->name,
                'type'        => $customerLeaflet->type->value,
                'type_label'  => LeafletTypeEnum::labels()[$customerLeaflet->type->value],
                'file_name'   => $customerLeaflet->media?->file_name,
                'mime_type'   => $customerLeaflet->media?->mime_type,
                'size'        => $customerLeaflet->media ? round($customerLeaflet->media->size / 1048576, 1).' MB' : null,
                'uploaded_at' => $customerLeaflet->updated_at?->format('d/m/Y'),
                'included'    => $customerLeaflet->state === LeafletStateEnum::ACTIVE,
            ])
            ->all();
    }

    public function getBreadcrumbs(): array
    {
        return array_merge(
            ShowRetinaFulfilmentSysAdminDashboard::make()->getBreadcrumbs(),
            [
                [
                    'type'   => 'simple',
                    'simple' => [
                        'route' => [
                            'name' => 'retina.sysadmin.inserts.show'
                        ],
                        'label' => __('Inserts'),
                    ]
                ]
            ]
        );
    }
}

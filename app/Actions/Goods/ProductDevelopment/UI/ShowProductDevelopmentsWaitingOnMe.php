<?php

/*
 * Author Louis Perez
 * Created on 17-10-2026-23h-45m
 * GitHub: https://github.com/louis-perez
 * Copyright 2026
*/

namespace App\Actions\Goods\ProductDevelopment\UI;

use App\Actions\Goods\ProductDevelopment\ProductDevelopmentWaitingOn;
use App\Actions\Goods\ProductDevelopment\UI\Traits\WithProductDevelopmentsIndex;
use App\Actions\OrgAction;
use App\Models\Goods\ProductDevelopment;
use App\Models\SysAdmin\User;
use App\Actions\Traits\Authorisations\WithProductDevelopmentAuthorisation;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

/**
 * Everything waiting on the signed-in person across their developments, built to be worked through
 * on a phone.
 */
class ShowProductDevelopmentsWaitingOnMe extends OrgAction
{
    use WithProductDevelopmentAuthorisation;
    use WithProductDevelopmentsIndex;

    public function asController(ActionRequest $request): Response
    {
        $this->initialisationFromGroup(group(), $request);

        return Inertia::render(
            'Goods/ProductDevelopmentsWaitingOnMe',
            [
                'title'       => __('Waiting on me'),
                'breadcrumbs' => $this->getBreadcrumbs(suffix: __('Waiting on me')),
                'pageHead'    => [
                    'title' => __('Waiting on me'),
                    'icon'  => [
                        'title' => __('Product development'),
                        'icon'  => 'fal fa-lightbulb-on',
                    ],
                ],
                'viewSwitch'  => $this->viewSwitch('waiting', count($rows = (new ProductDevelopmentWaitingOn())->forUser($request->user()))),
                'rows'        => $this->withFields($rows, $request->user()),
            ]
        );
    }

    /**
     * Gives each checklist row the fields it waits on, so they can be filled in from the card.
     *
     * @param array<int, array<string, mixed>> $rows
     *
     * @return array<int, array<string, mixed>>
     */
    private function withFields(array $rows, User $user): array
    {
        $editors = [];

        return array_map(function (array $row) use (&$editors, $user) {
            if (($row['kind'] ?? null) !== ProductDevelopmentWaitingOn::KIND_CHECKLIST) {
                return $row;
            }

            $slug = $row['development']['slug'];
            if (!isset($editors[$slug])) {
                $productDevelopment = ProductDevelopment::where('slug', $slug)->firstOrFail();
                $editor             = GetProductDevelopmentChecklistEditor::run($productDevelopment, $user, $this->canSeeProductDevelopmentCosts($user, $productDevelopment));
                $fields             = collect($editor['family'])->keyBy('id');
                foreach ($editor['skus'] as $sku) {
                    foreach ($sku['rows'] as $field) {
                        $fields->put($field['id'], [...$field, 'sku' => $sku['code']]);
                    }
                }
                $editors[$slug] = ['fields' => $fields, 'context' => ['countries' => $editor['countries'], 'options' => $editor['options'], 'update_route' => $editor['routes']['update']]];
            }

            return [
                ...$row,
                'fields'  => collect($row['items'])->map(fn (int $id) => $editors[$slug]['fields']->get($id))->filter()->values()->all(),
                'context' => $editors[$slug]['context'],
            ];
        }, $rows);
    }
}

<?php

/*
 * Author Louis Perez
 * Created on 15-10-2026-18h-05m
 * GitHub: https://github.com/louis-perez
 * Copyright 2026
*/

namespace App\Actions\Goods\ProductDevelopment\UI;

use App\Actions\Goods\ProductDevelopment\UI\Traits\WithProductDevelopmentsIndex;
use App\Actions\Goods\ProductDevelopment\UI\Traits\WithProductDevelopmentsQuery;
use App\Actions\OrgAction;
use App\Actions\Traits\Authorisations\WithProductDevelopmentAuthorisation;
use App\Enums\Goods\ProductDevelopment\ProductDevelopmentMemberRoleEnum;
use App\Enums\Goods\ProductDevelopment\ProductDevelopmentStageEnum;
use App\Http\Resources\Goods\ProductDevelopmentsResource;
use App\Models\SysAdmin\Group;
use App\Models\SysAdmin\User;
use Illuminate\Support\Collection;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class ShowProductDevelopmentsBoard extends OrgAction
{
    use WithProductDevelopmentAuthorisation;
    use WithProductDevelopmentsQuery;
    use WithProductDevelopmentsIndex;

    public const int MAX_CARDS = 500;

    public function asController(ActionRequest $request): Collection
    {
        $this->initialisationFromGroup(group(), $request);

        return $this->handle($this->group, $request->user());
    }

    public function handle(Group $group, User $user): Collection
    {
        return $this->productDevelopmentsQuery($group, $user)
            ->orderBy('product_developments.target_online_date')
            ->orderBy('product_developments.id')
            ->limit(self::MAX_CARDS)
            ->get();
    }

    public function htmlResponse(Collection $productDevelopments, ActionRequest $request): Response
    {
        $cards   = ProductDevelopmentsResource::collection($productDevelopments)->resolve();
        $columns = collect(ProductDevelopmentStageEnum::positionLabels())
            ->map(fn (string $label, int $position) => [
                'position' => $position,
                'label'    => $label,
                'cards'    => array_values(array_filter($cards, fn (array $card) => $card['stage_position'] === $position)),
            ])
            ->values()
            ->all();

        return Inertia::render(
            'Goods/ProductDevelopmentsBoard',
            [
                'breadcrumbs'   => $this->getBreadcrumbs('grp.product_developments.board', __('Board')),
                'title'         => __('Product development board'),
                'pageHead'      => [
                    'title'   => __('Product development'),
                    'icon'    => [
                        'title' => __('Product development'),
                        'icon'  => 'fal fa-lightbulb-on',
                    ],
                    'actions' => [
                        $this->createAction(),
                    ],
                ],
                'viewSwitch'    => $this->viewSwitch('board'),
                'columns'       => $columns,
                'filterBar'     => $this->productDevelopmentFilterBar($this->group, $request->user()),
                'roles'         => collect(ProductDevelopmentMemberRoleEnum::cases())->mapWithKeys(fn (ProductDevelopmentMemberRoleEnum $role) => [
                    $role->value => [
                        'label' => ProductDevelopmentMemberRoleEnum::labels()[$role->value],
                        'icon'  => $role->icon(),
                        'color' => $role->color(),
                    ],
                ])->all(),
                'truncated'     => $productDevelopments->count() >= self::MAX_CARDS,
            ]
        );
    }
}

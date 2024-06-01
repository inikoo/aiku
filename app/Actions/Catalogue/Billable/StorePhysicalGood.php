<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Wed, 29 Sep 2021 16:47:56 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2021, Inikoo
 *  Version 4.0
 */

namespace App\Actions\Catalogue\Billable;

use App\Actions\Catalogue\Outer\StoreOuter;
use App\Actions\SysAdmin\Group\Hydrators\GroupHydrateProducts;
use App\Actions\SysAdmin\Organisation\Hydrators\OrganisationHydrateProducts;
use App\Actions\Catalogue\Billable\Hydrators\BillableHydrateHistoricOuterables;
use App\Actions\Catalogue\Billable\Hydrators\BillableHydrateOuters;
use App\Actions\Catalogue\Billable\Hydrators\BillableHydrateUniversalSearch;
use App\Actions\Catalogue\Shop\Hydrators\ShopHydrateProducts;
use App\Actions\OrgAction;
use App\Enums\Catalogue\Billable\BillableStateEnum;
use App\Enums\Catalogue\Billable\BillableTypeEnum;
use App\Enums\Catalogue\Billable\BillableUnitRelationshipType;
use App\Models\Goods\TradeUnit;
use App\Models\Catalogue\Billable;
use App\Models\Catalogue\ProductCategory;
use App\Models\Catalogue\Shop;
use App\Rules\IUnique;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;

class StorePhysicalGood extends OrgAction
{
    private BillableStateEnum|null $state = null;
    private ProductCategory|Shop $parent;

    public function handle(Shop|ProductCategory $parent, array $modelData): Billable
    {
        // $modelData = $this->setDataFromParent($parent, $modelData);

        $tradeUnits = $modelData['trade_units'];
        data_forget($modelData, 'trade_units');

        data_set($modelData, 'unit_relationship_type', $this->getUnitRelationshipType($tradeUnits));
        data_set($modelData, 'outerable_type', 'Outer');


        /** @var Billable $product */
        $product = $parent->products()->create($modelData);
        $product->stats()->create();
        $product->salesIntervals()->create();


        $outer = StoreOuter::run(
            product: $product,
            modelData: [
                'code'               => $product->code,
                'price'              => $product->main_outerable_price,
                'unit'               => $product->main_outerable_unit,
                'name'               => $product->name,
                'is_main'            => true,
                'main_outer_ratio'   => 1,
                'source_id'          => $product->source_id,
                'historic_source_id' => $product->historic_source_id
            ]
        );



        foreach ($tradeUnits as $tradeUnitId => $tradeUnitData) {
            $tradeUnit = TradeUnit::find($tradeUnitId);
            $outer->tradeUnits()->attach(
                $tradeUnit,
                [
                    'units_per_main_outer' => $tradeUnitData['units_per_main_outer'],
                    'notes'                => Arr::get($tradeUnitData, 'notes'),
                ]
            );
        }


        SetProductMainOuter::run(
            product: $product,
            mainOuter: $outer
        );

        BillableHydrateHistoricOuterables::dispatch($product);
        BillableHydrateOuters::dispatch($product);

        ShopHydrateProducts::dispatch($product->shop);
        OrganisationHydrateProducts::dispatch($product->organisation);
        GroupHydrateProducts::dispatch($product->group);

        BillableHydrateUniversalSearch::dispatch($product);

        return $product;
    }

    public function getUnitRelationshipType(array $tradeUnits): ?BillableUnitRelationshipType
    {
        if (count($tradeUnits) == 1) {
            return BillableUnitRelationshipType::SINGLE;
        } elseif (count($tradeUnits) > 1) {
            return BillableUnitRelationshipType::MULTIPLE;
        }

        return null;
    }

    public function rules(): array
    {

        $rules = [
            'code'                 => [
                'required',
                'max:32',
                'alpha_dash',
                new IUnique(
                    table: 'billables',
                    extraConditions: [
                        ['column' => 'shop_id', 'value' => $this->shop->id],
                        ['column' => 'state', 'operator' => '!=', 'value' => BillableStateEnum::DISCONTINUED->value],
                        ['column' => 'deleted_at', 'operator' => 'notNull'],
                    ]
                ),
            ],
            'state'                => ['required', Rule::enum(BillableStateEnum::class)],
            'family_id'            => ['sometimes', 'required', 'exists:families,id'],
            'image_id'             => ['sometimes', 'required', 'exists:media,id'],
            'main_outerable_price' => ['required', 'numeric', 'min:0'],
            'main_outerable_unit'  => ['sometimes', 'required', 'string'],
            'rrp'                  => ['sometimes', 'required', 'numeric', 'min:0'],
            'name'                 => ['required', 'max:250', 'string'],
            'description'          => ['sometimes', 'required', 'max:1500'],
            'source_id'            => ['sometimes', 'required', 'string', 'max:255'],
            'historic_source_id'   => ['sometimes', 'required', 'string', 'max:255'],
            'type'                 => ['required', Rule::enum(BillableTypeEnum::class)],
            'owner_id'             => 'required',
            'owner_type'           => 'required',
            'status'               => ['required', 'boolean'],
            'data'                 => ['sometimes', 'array'],
            'settings'             => ['sometimes', 'array'],
            'created_at'           => ['sometimes', 'date'],

        ];

        if ($this->state == BillableStateEnum::DISCONTINUED) {
            $rules['code'] = [
                'required',
                'max:32',
                'alpha_dash',
            ];
        }

        if ($this->state == BillableStateEnum::DISCONTINUED or !$this->strict) {
            $rules['trade_units'] = ['sometimes', 'nullable', 'array'];

        } else {
            $rules['trade_units'] = ['required', 'array'];
        }




        return $rules;
    }

    private function prepareProductForValidation(): void
    {
        if ($this->parent instanceof ProductCategory) {
            $this->fill(
                [
                    'owner_type' => 'Shop',
                    'owner_id'   => $this->parent->shop_id
                ]
            );
        } elseif ($this->parent instanceof Shop) {
            $this->fill(
                [
                    'owner_type' => 'Shop',
                    'owner_id'   => $this->parent->id
                ]
            );
        }

        if (!$this->has('status')) {
            $this->set('status', true);
        }
    }

    public function prepareForValidation(ActionRequest $request): void
    {
        $this->set('type', BillableTypeEnum::PHYSICAL_GOOD);
        $this->prepareProductForValidation();

        if (!$this->has('state')) {
            $this->set('state', BillableStateEnum::IN_PROCESS);
        }
    }


    public function inShop(Shop $shop, ActionRequest $request): RedirectResponse
    {
        $request->validate();
        $this->handle($shop, $request->all());

        return Redirect::route('grp.org.shops.show.catalogue.products.index', $shop);
    }

    public function action(Shop|ProductCategory $parent, array $modelData, int $hydratorsDelay = 0, $strict = true): Billable
    {
        $this->hydratorsDelay = $hydratorsDelay;
        $this->asAction       = true;
        $this->state          = Arr::get($modelData, 'state');
        $this->strict         = $strict;
        $this->parent         = $parent;

        if ($parent instanceof Shop) {
            $shop = $parent;
        } else {
            $shop = $parent->shop;
        }

        $this->initialisationFromShop($shop, $modelData);

        return $this->handle($parent, $this->validatedData);
    }

}

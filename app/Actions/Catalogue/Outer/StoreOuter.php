<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 08 Apr 2024 09:52:43 Central Indonesia Time, Bali Office , Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Catalogue\Outer;

use App\Actions\Catalogue\HistoricOuterable\StoreHistoricOuterable;
use App\Actions\Catalogue\Outer\Hydrators\OuterHydrateUniversalSearch;
use App\Actions\Catalogue\Billable\Hydrators\BillableHydrateHistoricOuterables;
use App\Actions\Catalogue\Billable\Hydrators\BillableHydrateOuters;
use App\Actions\OrgAction;
use App\Enums\Catalogue\Outer\OuterStateEnum;
use App\Enums\Catalogue\Billable\BillableStateEnum;
use App\Models\Catalogue\Outer;
use App\Models\Catalogue\Billable;
use App\Rules\AlphaDashDot;
use App\Rules\IUnique;
use Illuminate\Validation\Rule;

class StoreOuter extends OrgAction
{
    public function handle(Billable $product, array $modelData, bool $skipHistoric = false): Outer
    {

        data_set($modelData, 'organisation_id', $product->organisation_id);
        data_set($modelData, 'group_id', $product->group_id);
        data_set($modelData, 'shop_id', $product->shop_id);
        data_set($modelData, 'state', match ($product->state) {
            BillableStateEnum::IN_PROCESS     => OuterStateEnum::IN_PROCESS,
            BillableStateEnum::ACTIVE         => OuterStateEnum::ACTIVE,
            BillableStateEnum::DISCONTINUING  => OuterStateEnum::DISCONTINUING,
            BillableStateEnum::DISCONTINUED   => OuterStateEnum::DISCONTINUED,
        });
        data_set($modelData, 'price', $product->main_outerable_price);

        /** @var Outer $outer */
        $outer = $product->outers()->create($modelData);
        $outer->salesIntervals()->create();


        if (!$skipHistoric) {
            $historicProduct = StoreHistoricOuterable::run($outer, [
                'source_id'=> $outer->historic_source_id
            ]);
            $product->update(
                [
                    'current_historic_outerable_id' => $historicProduct->id
                ]
            );
        }

        BillableHydrateOuters::dispatch($product);
        BillableHydrateHistoricOuterables::dispatch($product);
        OuterHydrateUniversalSearch::dispatch($outer);

        return $outer;
    }

    public function rules(): array
    {
        return [
            'is_main'     => ['required', 'boolean'],
            'code'        => [
                'required',
                'max:32',
                new AlphaDashDot(),
                new IUnique(
                    table: 'outers',
                    extraConditions: [
                        ['column' => 'shop_id', 'value' => $this->shop->id],
                        ['column' => 'deleted_at', 'operator'=>'notNull'],
                    ]
                ),
            ],
            'units'       => ['sometimes', 'required', 'numeric'],
            'name'        => ['required', 'max:250', 'string'],

            'price'                => ['required', 'numeric'],
            'source_id'            => ['sometimes', 'required', 'string', 'max:255'],
            'historic_source_id'   => ['sometimes', 'required', 'string', 'max:255'],
            'state'                => ['required', Rule::enum(OuterStateEnum::class)],
            'data'                 => ['sometimes', 'array'],
            'created_at'           => ['sometimes', 'date'],
        ];

    }

    public function action(Billable $product, array $modelData, int $hydratorsDelay = 0): Outer
    {
        $this->hydratorsDelay = $hydratorsDelay;
        $this->asAction       = true;


        $this->initialisationFromShop($product->shop, $modelData);

        return $this->handle($product, $this->validatedData);
    }




}

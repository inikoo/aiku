<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Tue, 18 Apr 2023 15:08:02 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Ordering\ShippingZoneSchema;

use App\Actions\OrgAction;
use App\Enums\Ordering\ShippingZoneSchema\ShippingZoneSchemaTypeEnum;
use App\Models\Ordering\ShippingZoneSchema;
use App\Models\Catalogue\Shop;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;

class StoreShippingZoneSchema extends OrgAction
{
    /**
     * @throws \Throwable
     */
    public function handle(Shop $shop, array $modelData): ShippingZoneSchema
    {
        data_set($modelData, 'group_id', $shop->group_id);
        data_set($modelData, 'organisation_id', $shop->organisation_id);

        return DB::transaction(function () use ($shop, $modelData) {
            /** @var $shippingZoneSchema ShippingZoneSchema */
            $shippingZoneSchema = $shop->shippingZoneSchemas()->create($modelData);
            $shippingZoneSchema->stats()->create();

            return $shippingZoneSchema;
        });
    }

    public function rules(): array
    {
        return [
            'name'       => ['required', 'max:255', 'string'],
            'type'       => ['sometimes', Rule::enum(ShippingZoneSchemaTypeEnum::class)],
            'fetched_at' => ['sometimes', 'date'],
            'source_id'  => ['sometimes', 'nullable', 'string'],
            'created_at' => ['sometimes', 'nullable', 'date'],
        ];
    }

    /**
     * @throws \Throwable
     */
    public function action(Shop $shop, array $modelData, int $hydratorsDelay = 0, bool $strict = true, $audit = true): ShippingZoneSchema
    {
        if (!$audit) {
            ShippingZoneSchema::disableAuditing();
        }
        $this->strict         = $strict;
        $this->asAction       = true;
        $this->hydratorsDelay = $hydratorsDelay;
        $this->initialisationFromShop($shop, $modelData);

        return $this->handle($shop, $this->validatedData);
    }

    /**
     * @throws \Throwable
     */
    public function asController(Shop $shop, ActionRequest $request): ShippingZoneSchema
    {
        $this->initialisationFromShop($shop, $request);

        return $this->handle($shop, $this->validatedData);
    }

    public function htmlResponse(ShippingZoneSchema $shippingZoneSchema): RedirectResponse
    {
        return Redirect::route('grp.org.shops.show.assets.shipping.show', [
            'organisation'       => $shippingZoneSchema->organisation->slug,
            'shop'               => $shippingZoneSchema->shop->slug,
            'shippingZoneSchema' => $shippingZoneSchema->slug
        ]);
    }
}

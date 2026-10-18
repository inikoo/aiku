<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 15 Oct 2026, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Mcp\Tools;

use App\Actions\Goods\Barcode\StoreBarcode;
use App\Actions\Goods\Barcode\SyncBarcodeToTradeUnit;
use App\Actions\Goods\TradeUnit\UpdateTradeUnit;
use App\Actions\SysAdmin\McpChange\GetMcpChangeSnapshot;
use App\Enums\Helpers\Barcode\BarcodeStatusEnum;
use App\Enums\Helpers\Barcode\BarcodeTypeEnum;
use App\Enums\SysAdmin\McpChange\McpChangeTypeEnum;
use App\Models\Goods\TradeUnit;
use App\Models\Helpers\Barcode;
use App\Models\Helpers\Country;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;

/**
 * Trade unit properties are gated by can_use_mcp_goods on the user's account, on top of the goods edit
 * permission the same change needs in the UI. A barcode is only ever given to a trade unit without one,
 * and never taken from another trade unit or master: those clashes are for a person to settle.
 */
#[Description('Shows or changes the properties of a trade unit (the single item as made: what one barcode is printed on). Without changes it shows them. With changes it saves: name, description, gross_weight, net_weight and marketing_weight in grams, marketing_ingredients, origin_country (ISO code), tariff_code, duty_rate, hts_us, cpnp_number, ufi_number, scpn_number, un_number, un_class, packing_group, proper_shipping_name. With barcode it gives a trade unit that has none the EAN printed on it: a number already on another trade unit or master is refused and reported, and a trade unit that already has a barcode is refused, since moving or clearing barcodes is done by a person in Goods > Barcodes. Only save after showing the user the old and new values and they confirmed in their own words, passing their request text. Every save is logged in AI changes; properties can be reverted there, a barcode given cannot. Only for users enrolled to edit trade units through their assistant.')]
class TradeUnitPropertiesTool extends Tool
{
    use WithMcpPermissions;
    use WithMcpChangeLog;

    public function handle(Request $request): Response
    {
        $request->validate([
            'code'         => ['required', 'string'],
            'changes'      => ['sometimes', 'array', 'min:1'],
            'barcode'      => ['sometimes', 'string'],
            'request_text' => ['required_with:changes,barcode', 'string', 'max:4000'],
        ]);

        $user = $request->user();
        if (!$user?->can_use_mcp_goods) {
            return Response::error('Editing trade units is not enabled for this user. Do not retry; an administrator enrols it on the user\'s edit page.');
        }
        if (!$this->userCan($request, 'goods.edit')) {
            return Response::error('Trade units need goods edit permission, which this user does not have. Nothing was changed.');
        }

        $given     = strtolower(trim((string) $request->string('code')));
        $tradeUnit = TradeUnit::where('group_id', $user->group_id)
            ->where(fn ($query) => $query->whereRaw('lower(code) = ?', [$given])->orWhere('slug', $given))
            ->first();
        if (!$tradeUnit) {
            return Response::error("'$given' is not a trade unit.");
        }

        $target = ['trade_unit_id' => $tradeUnit->id, 'code' => $tradeUnit->code];

        if (!$request->has('changes') && !$request->has('barcode')) {
            return Response::json([
                'code'        => $tradeUnit->code,
                'properties'  => GetMcpChangeSnapshot::run(McpChangeTypeEnum::TRADE_UNIT_PROPERTIES, $target),
                'ingredients' => $tradeUnit->ingredients()->pluck('name')->all(),
            ]);
        }

        $changes = $request->get('changes', []);
        if (array_key_exists('origin_country', $changes)) {
            $isoCode = strtoupper(trim((string) Arr::pull($changes, 'origin_country')));
            $country = $isoCode ? Country::where(strlen($isoCode) === 3 ? 'iso3' : 'code', $isoCode)->first() : null;
            if ($isoCode && !$country) {
                return Response::error("Nothing was changed. '$isoCode' is not a country ISO code.");
            }
            $changes['origin_country_id'] = $country?->id;
        }

        $unknown = array_diff(array_keys($changes), GetMcpChangeSnapshot::TRADE_UNIT_FIELDS);
        if ($unknown) {
            return Response::error('Nothing was changed. These can not be changed here: '.implode(', ', $unknown).'.');
        }

        $barcodeNumber = $request->has('barcode') ? preg_replace('/\s+/', '', (string) $request->string('barcode')) : null;
        if ($barcodeNumber !== null && $refusal = $this->barcodeRefusal($tradeUnit, $barcodeNumber)) {
            return Response::error('Nothing was changed. '.$refusal);
        }

        try {
            $this->recordChange(
                $request,
                McpChangeTypeEnum::TRADE_UNIT_PROPERTIES,
                'Trade unit '.$tradeUnit->code.': '.collect(array_keys($changes))->when($barcodeNumber, fn ($fields) => $fields->push('barcode'))->implode(', '),
                $target,
                fn () => DB::transaction(function () use ($tradeUnit, $changes, $barcodeNumber) {
                    if ($changes) {
                        UpdateTradeUnit::make()->action($tradeUnit, $changes);
                    }
                    if ($barcodeNumber) {
                        $this->giveBarcode($tradeUnit, $barcodeNumber);
                    }
                })
            );
        } catch (ValidationException $exception) {
            return Response::error('Nothing was changed. '.implode(' ', $exception->validator->errors()->all()));
        }

        return Response::json([
            'changed'       => (bool) $this->mcpChange,
            'change_log_id' => $this->mcpChange?->id,
            'before'        => $this->mcpChange?->data['before_text'],
            'after'         => $this->mcpChange?->data['after_text'],
        ]);
    }

    private function barcodeRefusal(TradeUnit $tradeUnit, string $number): ?string
    {
        if ($tradeUnit->barcode_id || $tradeUnit->barcode) {
            return "$tradeUnit->code already has barcode $tradeUnit->barcode; changing it is done by a person in Goods > Barcodes.";
        }

        if (!StoreBarcode::make()->hasValidCheckDigit($number) || !preg_match('/^\d{8,14}$/', $number)) {
            return "$number is not a valid EAN: check the number for typos.";
        }

        $barcode = Barcode::where('group_id', $tradeUnit->group_id)->where('number', $number)->first();

        $holders = DB::table('trade_units')->where('barcode', $number)->pluck('code')
            ->merge($barcode ? $barcode->tradeUnitsActive()->pluck('code') : [])
            ->merge(DB::table('master_assets')->where('barcode', $number)->whereNull('deleted_at')->pluck('code')->map(fn ($code) => "master $code"))
            ->unique();

        if ($holders->isNotEmpty()) {
            return "$number is already on ".$holders->implode(', ').'. A barcode belongs to one item: ask the user to check the printed labels and settle it in Goods > Barcodes.';
        }

        return null;
    }

    private function giveBarcode(TradeUnit $tradeUnit, string $number): void
    {
        $barcode = Barcode::where('group_id', $tradeUnit->group_id)->where('number', $number)->first();

        if ($barcode) {
            SyncBarcodeToTradeUnit::make()->action($barcode, $tradeUnit);

            return;
        }

        StoreBarcode::make()->action($tradeUnit->group, [
            'number'     => $number,
            'status'     => BarcodeStatusEnum::USED,
            'type'       => BarcodeTypeEnum::EAN,
            'data'       => ['external' => true],
            'trade_unit' => $tradeUnit->id,
        ]);
    }

    /**
     * @return array<string, JsonSchema>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'code'         => $schema->string()->description('Trade unit code, e.g. FOKG-223')->required(),
            'changes'      => $schema->object()->description('Properties to save, e.g. {"gross_weight": 25, "marketing_ingredients": "Lavandula angustifolia oil"}. Weights in grams. Omit to only show'),
            'barcode'      => $schema->string()->description('EAN printed on the item, for a trade unit that has no barcode yet'),
            'request_text' => $schema->string()->description('The user\'s request, verbatim; required when saving'),
        ];
    }
}

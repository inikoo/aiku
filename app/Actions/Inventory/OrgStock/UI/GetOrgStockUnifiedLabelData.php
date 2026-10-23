<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 13 Oct 2026
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Inventory\OrgStock\UI;

use App\Models\Catalogue\Product;
use App\Models\Goods\Stock;
use App\Models\Goods\TradeUnit;
use App\Models\Inventory\OrgStock;
use App\Models\SupplyChain\SupplierProduct;
use App\Models\SysAdmin\Organisation;
use Illuminate\Support\Facades\DB;
use Locale;
use Lorisleiva\Actions\Concerns\AsObject;

/**
 * The unified label (HELP-3937): one unit, SKO and carton label for every organisation, so stock
 * moves between centres without relabelling. Nothing on it is typed except the batch, and a label
 * with a required value missing comes back with the reason in blockers instead of printing a gap.
 */
class GetOrgStockUnifiedLabelData
{
    use AsObject;

    public const SIZES = [
        'unit'   => ['width' => 100.0, 'height' => 60.0],
        'sko'    => ['width' => 100.0, 'height' => 50.0],
        'carton' => ['width' => 100.0, 'height' => 70.0],
    ];

    public const LANGUAGES = ['es', 'sk', 'cs', 'de', 'fr', 'it', 'nl', 'pl', 'pt', 'ro', 'hu', 'hr', 'sv', 'bg'];

    public const MANDATORY_LANGUAGES = ['es', 'sk'];

    public const BATCH_PATTERN = '/^[A-Z0-9-]{1,20}$/';

    /**
     * @return array<string, mixed>
     */
    public function handle(OrgStock $orgStock, string $level = 'unit', ?SupplierProduct $supplierProduct = null, ?string $batch = null): array
    {
        $level     = isset(self::SIZES[$level]) ? $level : 'unit';
        $base      = GetOrgStockLabelData::run($orgStock, 'carton', $supplierProduct);
        $tradeUnit = $orgStock->tradeUnits->count() === 1 ? $orgStock->tradeUnits->first() : null;
        $ean       = (string) $orgStock->unit_barcode;
        $validEan  = GetOrgStockLabelData::make()->isValidEan13($ean);

        $unitsPerPack   = (int) ($orgStock->packed_in ?? 1);
        $unitsPerCarton = (int) ($base['carton']['units_per_carton'] ?? 0);
        $batch          = filled($batch) ? strtoupper(trim($batch)) : $this->getDefaultBatch($base['carton']['batch_code'] ?? null);
        $indicator      = $validEan ? $this->getIndicator($orgStock, $ean, $level) : null;
        $gtin14         = $indicator ? self::gtin14($indicator, $ean) : null;
        $names          = $this->getNames($tradeUnit);
        $warnings       = $this->getWarnings($orgStock, $tradeUnit);
        $madeIn         = $this->getMadeIn($tradeUnit);

        $label = [
            'level'            => $level,
            'size'             => self::SIZES[$level],
            'code'             => $orgStock->code,
            'name'             => $tradeUnit?->name,
            'names'            => $names,
            'made_in'          => $madeIn,
            'origin'           => $base['carton']['origin'] ?? null,
            'material'         => $base['materials'],
            'batch'            => $batch,
            'warnings'         => $warnings,
            'unit_ean13'       => $ean ?: null,
            'units_per_pack'   => $unitsPerPack,
            'packs_per_carton' => $unitsPerCarton && $unitsPerPack ? trimDecimalZeros(round($unitsPerCarton / $unitsPerPack, 2)) : null,
            'units_per_carton' => $unitsPerCarton ?: null,
            'weights'          => $this->getCartonWeights($supplierProduct ?? GetOrgStockLabelData::make()->getSupplierProduct($orgStock)),
            'hs_code'          => $this->getHsCode($tradeUnit),
            'gtin14'           => $level === 'unit' ? null : $gtin14,
            'gs1_128_data'     => $level === 'unit' || !$gtin14 ? null : chr(241).'01'.$gtin14.'10'.$batch,
            'hri'              => $level === 'unit' || !$gtin14 ? null : '(01) '.$gtin14.' (10) '.$batch,
            'operators'        => $this->getOperators($orgStock),
        ];

        $label['blockers'] = $this->getBlockers($label, $tradeUnit, $validEan, $indicator);

        return $label;
    }

    /**
     * Every importer in the group is printed whoever prints the label, so stock moves between
     * centres without relabelling.
     *
     * @return array<int, array{code: string, role: string, name: string, short: string, address: string}>
     */
    private function getOperators(OrgStock $orgStock): array
    {
        return Organisation::where('group_id', $orgStock->group_id)
            ->get()
            ->map(fn (Organisation $organisation) => $organisation->getGpsrOperator())
            ->filter(fn (?array $operator) => filled($operator['code'] ?? null) && filled($operator['address'] ?? null))
            ->sortBy('position')
            ->map(fn (array $operator) => [
                'code'    => $operator['code'],
                'role'    => $operator['role'] ?? '',
                'name'    => $operator['name'],
                'short'   => $operator['short'] ?? $operator['name'],
                'address' => implode(' · ', array_filter([$operator['address'], $operator['email'] ?? null], 'filled')),
            ])
            ->values()
            ->all();
    }

    /**
     * GS1 mod-10 over the indicator and the first twelve digits of the unit EAN, so a pack and a
     * carton get a number of their own without buying one.
     */
    public static function gtin14(int $indicator, string $ean13): string
    {
        $body = $indicator.substr($ean13, 0, 12);
        $sum  = 0;

        foreach (str_split(strrev($body)) as $index => $digit) {
            $sum += (int) $digit * ($index % 2 === 0 ? 3 : 1);
        }

        return $body.((10 - $sum % 10) % 10);
    }

    /**
     * What a scanner reads off a unified SKO or carton label: AI 01 the GTIN-14, AI 10 the batch.
     * Scanners differ on the symbology prefix and some are set to send the brackets.
     *
     * @return array{gtin14: string, indicator: int, unit_ean13: string, batch: ?string}|null
     */
    public static function parseGs1(string $scan): ?array
    {
        $scan = preg_replace('/^\]C1|[()\s\x1D]/', '', trim($scan));

        if (!preg_match('/^01(\d{14})(?:10([A-Za-z0-9-]{1,20}))?$/', $scan, $matches) || self::gtin14((int) $matches[1][0], substr($matches[1], 1)) !== $matches[1]) {
            return null;
        }

        $body = substr($matches[1], 1, 12);
        $sum  = 0;

        foreach (str_split($body) as $index => $digit) {
            $sum += (int) $digit * ($index % 2 === 0 ? 1 : 3);
        }

        return [
            'gtin14'     => $matches[1],
            'indicator'  => (int) $matches[1][0],
            'unit_ean13' => $body.((10 - $sum % 10) % 10),
            'batch'      => $matches[2] ?? null,
        ];
    }

    /**
     * The stocks holding the unit behind a GTIN-14 indicator, in the order the indicators are given
     * out, so a scan lands on the pack size the label was printed for.
     *
     * @return array<int, int>
     */
    public static function getStockIdsOfUnit(string $ean13): array
    {
        return Stock::whereHas('tradeUnits', fn ($query) => $query->where('trade_units.barcode', $ean13))
            ->orderBy('id')
            ->pluck('id')
            ->all();
    }

    /**
     * Indicator 1 is the SKO and 2 its carton. A second pack size of the same unit takes 3 and 4,
     * and so on up to 8, because a different quantity must never scan as the same GTIN.
     *
     * ponytail: the indicator is the stock's place among the stocks sharing the unit EAN (11 EANs
     * on 13 Oct 2026), so deleting an older sibling renumbers the newer one. Store the GTIN-14 on
     * stocks if that ever happens to a stock with printed labels.
     */
    private function getIndicator(OrgStock $orgStock, string $ean, string $level): ?int
    {
        $rank = (int) array_search($orgStock->stock_id, self::getStockIdsOfUnit($ean), true);

        if ($rank > 3) {
            return null;
        }

        return 2 * $rank + ($level === 'carton' ? 2 : 1);
    }

    /**
     * @return array<string, string> the blocker for each mandatory language the name is missing in
     */
    public function getMissingNameBlockers(TradeUnit $tradeUnit): array
    {
        $names    = $this->getNames($tradeUnit);
        $blockers = [];

        foreach (self::MANDATORY_LANGUAGES as $language) {
            if (!isset($names[$language])) {
                $blockers[$language] = __('The name in :language is missing', ['language' => strtoupper($language)]);
            }
        }

        return $blockers;
    }

    /**
     * @return array<string, string>
     */
    private function getNames(?TradeUnit $tradeUnit): array
    {
        if (!$tradeUnit) {
            return [];
        }

        $translations = $tradeUnit->getTranslations('name_i8n');
        $names        = [];

        foreach (self::LANGUAGES as $language) {
            $name = trim(preg_replace('/\s+/', ' ', (string) ($translations[$language] ?? '')));

            if ($name !== '') {
                $names[$language] = $name;
            }
        }

        return $names;
    }

    /**
     * The product record keeps one warning text and its translations sit on the products sold from
     * it, so they are gathered from the products of every organisation: the label reads the same
     * whoever prints it.
     *
     * @return array<string, string>
     */
    private function getWarnings(OrgStock $orgStock, ?TradeUnit $tradeUnit): array
    {
        $warnings = [];

        if ($orgStock->stock_id) {
            Product::whereIn('id', DB::table('product_has_org_stocks')
                ->whereIn('org_stock_id', OrgStock::where('stock_id', $orgStock->stock_id)->select('id'))
                ->select('product_id'))
                ->whereNotNull('gpsr_warnings_i8n')
                ->orderBy('id')
                ->get(['id', 'gpsr_warnings_i8n'])
                ->each(function (Product $product) use (&$warnings) {
                    $warnings += array_filter(array_map(fn ($text) => trim(preg_replace('/\s+/', ' ', (string) $text)), $product->getTranslations('gpsr_warnings_i8n')), 'filled');
                });
        }

        if ($warnings === [] && blank($tradeUnit?->gpsr_warnings)) {
            return [];
        }

        $ordered = [];

        foreach (['en', ...self::LANGUAGES] as $language) {
            if (isset($warnings[$language])) {
                $ordered[$language] = $warnings[$language];
            }
        }

        return $ordered + ['en' => null];
    }

    /**
     * Slovak declines the country after "made in", which no list of country names gives, so the
     * Slovak line states the country of origin instead and stays right for every country.
     */
    private function getMadeIn(?TradeUnit $tradeUnit): ?string
    {
        $country = $tradeUnit?->countryOrigin;

        if (!$country) {
            return null;
        }

        return implode(' · ', [
            'Made in '.$country->name,
            'Hecho en '.Locale::getDisplayRegion('-'.$country->code, 'es'),
            'Krajina pôvodu: '.Locale::getDisplayRegion('-'.$country->code, 'sk'),
        ]);
    }

    private function getDefaultBatch(?string $batch): ?string
    {
        $batch = substr(preg_replace('/[^A-Z0-9-]/', '', strtoupper((string) $batch)), 0, 20);

        return $batch === '' ? null : $batch;
    }

    /**
     * A carton weight is printed only when the supplier product holds both and they make sense: a
     * net weight above the gross is a typing mistake, and a customs officer reads this line.
     *
     * @return array{net: ?string, gross: ?string}
     */
    private function getCartonWeights(?SupplierProduct $supplierProduct): array
    {
        $net   = (int) $supplierProduct?->carton_net_weight;
        $gross = (int) $supplierProduct?->carton_weight;

        if ($net <= 0 || $gross <= 0 || $net > $gross) {
            return ['net' => null, 'gross' => null];
        }

        return [
            'net'   => trimDecimalZeros(round($net / 1000, 2)).' kg',
            'gross' => trimDecimalZeros(round($gross / 1000, 2)).' kg',
        ];
    }

    private function getHsCode(?TradeUnit $tradeUnit): ?string
    {
        $digits = substr(preg_replace('/\D/', '', (string) $tradeUnit?->tariff_code), 0, 8);

        return strlen($digits) === 8 ? substr($digits, 0, 4).' '.substr($digits, 4, 2).' '.substr($digits, 6, 2) : null;
    }

    /**
     * @param  array<string, mixed>  $label
     * @return array<int, string>
     */
    private function getBlockers(array $label, ?TradeUnit $tradeUnit, bool $validEan, ?int $indicator): array
    {
        $blockers = [];

        if (!$tradeUnit) {
            $blockers[] = __('This SKO does not hold exactly one trade unit');
        }

        if (!$label['operators']) {
            $blockers[] = __('No organisation has its importer details set');
        }

        if (blank($label['unit_ean13'])) {
            $blockers[] = __('The unit has no EAN-13 barcode');
        } elseif (!$validEan) {
            $blockers[] = __('The unit barcode :barcode is not a valid EAN-13', ['barcode' => $label['unit_ean13']]);
        } elseif (!$indicator) {
            $blockers[] = __('More than four pack sizes share the unit barcode :barcode', ['barcode' => $label['unit_ean13']]);
        }

        if ($tradeUnit) {
            $blockers = array_merge($blockers, array_values($this->getMissingNameBlockers($tradeUnit)));
        }

        if (blank($label['batch'])) {
            $blockers[] = __('The batch code is missing');
        } elseif (!preg_match(self::BATCH_PATTERN, $label['batch'])) {
            $blockers[] = __('The batch code takes up to 20 characters: A to Z, 0 to 9 and hyphen');
        }

        if ($tradeUnit && blank($label['made_in'])) {
            $blockers[] = __('The country of origin is missing');
        }

        foreach ($label['warnings'] ? ['en', ...self::MANDATORY_LANGUAGES] : [] as $language) {
            if (blank($label['warnings'][$language] ?? null)) {
                $blockers[] = __('The product has warnings but none in :language', ['language' => strtoupper($language)]);
            }
        }

        if ($label['level'] === 'sko' && $label['units_per_pack'] <= 1) {
            $blockers[] = __('A pack of one has no SKO label: the unit label is its label');
        }

        if ($label['level'] === 'carton' && !$label['units_per_carton']) {
            $blockers[] = __('The supplier product has no units per carton');
        }

        return $blockers;
    }
}

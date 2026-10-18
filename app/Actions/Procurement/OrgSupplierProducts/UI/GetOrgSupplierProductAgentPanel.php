<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 15 Oct 2026 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Procurement\OrgSupplierProducts\UI;

use App\Actions\SupplyChain\SupplierStockFamily\GetProductionCalendarAdvice;
use App\Enums\SupplyChain\SupplierProduct\PackagingTermPayerEnum;
use App\Enums\SupplyChain\SupplierProduct\SupplierProductAvailabilityStatusEnum;
use App\Enums\SupplyChain\SupplierProduct\SupplierProductUnavailableReasonEnum;
use App\Models\Goods\TradeUnit;
use App\Models\Procurement\OrgSupplierProduct;
use App\Models\SupplyChain\SupplierProduct;
use App\Models\SupplyChain\SupplierStockFamily;
use App\Models\SysAdmin\Organisation;
use App\Models\SysAdmin\User;
use Illuminate\Support\Arr;
use Lorisleiva\Actions\Concerns\AsObject;

/**
 * What an agent needs on a product page to fix the data freight costing and slotting depend on.
 * Values a product does not set itself come from its family at the supplier, then from the supplier.
 */
class GetOrgSupplierProductAgentPanel
{
    use AsObject;

    public const array CONTAINERS_CBM = ['20ft' => 28, '40ft' => 58, '40ft_hc' => 68];

    private const array PAID_PACKAGING_TERMS = ['retail_box', 'extra_wrapping', 'special_packaging', 'label_application'];

    public function handle(OrgSupplierProduct $orgSupplierProduct, Organisation $viewingOrganisation, bool $canEdit): array
    {
        $supplierProduct = $orgSupplierProduct->supplierProduct;
        $supplier        = $supplierProduct->supplier;
        $stock           = $supplierProduct->stocks->first();
        $family          = $stock?->stockFamily;
        $tradeUnit       = $supplierProduct->tradeUnits->first();

        $familyAtSupplier = $family
            ? SupplierStockFamily::where('supplier_id', $supplierProduct->supplier_id)->where('stock_family_id', $family->id)->first()
            : null;

        $moq            = $this->inherited($supplierProduct->minimum_carton_order, $familyAtSupplier?->minimum_carton_order);
        $leadTime       = $this->inherited($supplierProduct->estimated_lead_time_days, $familyAtSupplier?->estimated_lead_time_days, $supplier->defaultLeadTimeDays());
        $packagingTerms = $this->packagingTerms($supplierProduct, $familyAtSupplier);

        $cbm            = $supplierProduct->cbm !== null ? (float) $supplierProduct->cbm : null;
        $unitsPerCarton = $supplierProduct->units_per_carton;
        $unitCost       = (float) $supplierProduct->cost;

        $contacts = [
            'contact_name' => $supplier->contact_name,
            'phone'        => $supplier->phone,
            'email'        => $supplier->email,
            'wechat'       => $supplier->wechat,
            'whatsapp'     => $supplier->whatsapp,
        ];

        $hasDirectContact = $supplier->phone || $supplier->email || $supplier->wechat || $supplier->whatsapp;

        return [
            'internal_code' => $stock?->code ?? $supplierProduct->code,
            'supplier_code' => $supplierProduct->code,
            'breadcrumb'    => [
                'supplier' => ['code' => $supplier->code, 'name' => $supplier->name],
                'family'   => $family ? ['code' => $family->code, 'name' => $family->name] : null,
            ],
            'currency'      => $supplierProduct->currency->code,
            'key'           => [
                'cbm'              => $cbm,
                'units_per_carton' => $unitsPerCarton,
                'moq'              => [
                    'cartons' => $moq['value'],
                    'source'  => $moq['source'],
                    'units'   => $moq['value'] && $unitsPerCarton ? $moq['value'] * $unitsPerCarton : null,
                    'cbm'     => $moq['value'] && $cbm ? round($moq['value'] * $cbm, 3) : null,
                    'value'   => $moq['value'] && $unitsPerCarton ? round($moq['value'] * $unitsPerCarton * $unitCost, 2) : null,
                ],
                'lead_time'        => [
                    'days'         => $leadTime['value'],
                    'source'       => $leadTime['source'],
                    'measured'     => $supplierProduct->measured_lead_time_days,
                    'samples'      => $supplierProduct->lead_time_samples,
                    'confirmed_at' => $supplierProduct->lead_time_confirmed_at?->toIso8601String(),
                    'confirmed_by' => $supplierProduct->lead_time_confirmed_by_user_id
                        ? User::find($supplierProduct->lead_time_confirmed_by_user_id)?->contact_name
                        : null,
                ],
                'cost'             => [
                    'unit'   => $unitCost,
                    'carton' => $unitsPerCarton ? round($unitCost * $unitsPerCarton, 2) : null,
                ],
            ],
            'measurements'  => [
                'unit'   => [
                    'dimensions'   => $this->unitDimensionsInCm($tradeUnit),
                    'net_weight'   => $tradeUnit?->net_weight,
                    'gross_weight' => $tradeUnit?->gross_weight,
                ],
                'carton' => [
                    'dimensions'   => $supplierProduct->carton_dimensions,
                    'net_weight'   => $supplierProduct->carton_net_weight,
                    'gross_weight' => $supplierProduct->carton_weight,
                    'cbm'          => $cbm,
                ],
            ],
            'warnings'      => array_values(array_filter([
                ...$this->cbmWarnings($supplierProduct, $tradeUnit, $cbm, $unitsPerCarton),
                $this->weightWarning($supplierProduct, $tradeUnit, $unitsPerCarton),
            ])),
            'cbm_siblings'  => $this->cbmSiblings($supplierProduct, $cbm),
            'containers'    => $cbm ? array_map(fn (int $capacity) => (int) floor($capacity / $cbm), self::CONTAINERS_CBM) : null,
            'checks'        => [
                ['key' => 'cbm', 'label' => __('CBM'), 'done' => (bool) $cbm, 'anchor' => 'measurements'],
                ['key' => 'carton_dimensions', 'label' => __('Carton dimensions'), 'done' => (bool) $supplierProduct->carton_dimensions, 'anchor' => 'measurements'],
                ['key' => 'carton_gross_weight', 'label' => __('Carton gross weight'), 'done' => (bool) $supplierProduct->carton_weight, 'anchor' => 'measurements'],
                ['key' => 'carton_net_weight', 'label' => __('Carton net weight'), 'done' => (bool) $supplierProduct->carton_net_weight, 'anchor' => 'measurements'],
                ['key' => 'moq', 'label' => __('MOQ'), 'done' => (bool) $moq['value'], 'anchor' => 'key-figures'],
                ['key' => 'unit_size', 'label' => __('Unit size'), 'done' => (bool) $this->unitDimensionsInCm($tradeUnit), 'anchor' => 'measurements'],
                ['key' => 'lead_time_confirmed', 'label' => __('Lead time confirmed'), 'done' => (bool) $supplierProduct->lead_time_confirmed_at, 'anchor' => 'key-figures'],
                ['key' => 'direct_contact', 'label' => __('Direct contact'), 'done' => $hasDirectContact, 'anchor' => 'contacts'],
                ['key' => 'packaging_terms', 'label' => __('Packaging terms'), 'done' => collect(self::PAID_PACKAGING_TERMS)->every(fn (string $term) => filled($packagingTerms[$term]['value'] ?? null)), 'anchor' => 'packaging'],
            ],
            'contacts'      => [
                'values'       => $contacts,
                'update_route' => $canEdit ? $this->route('grp.models.org.org_supplier_product.supplier_contacts.update', $orgSupplierProduct, $viewingOrganisation) : null,
            ],
            'packaging'     => [
                'terms'   => $packagingTerms,
                'options' => PackagingTermPayerEnum::labels(),
            ],
            'production_calendar' => [
                'months' => (object) ($familyAtSupplier?->production_calendar ?? []),
                'advice' => GetProductionCalendarAdvice::run($familyAtSupplier?->production_calendar ?? [], $leadTime['value']),
                'family' => $family?->code,
            ],
            'availability'  => [
                'status'          => $supplierProduct->availability_status->value,
                'reason'          => $supplierProduct->unavailable_reason?->value,
                'available_from'  => $supplierProduct->available_from?->toDateString(),
                'new_cost'        => $supplierProduct->availability_new_cost !== null ? (float) $supplierProduct->availability_new_cost : null,
                'note'            => $supplierProduct->availability_note,
                'status_options'  => SupplierProductAvailabilityStatusEnum::labels(),
                'reason_options'  => SupplierProductUnavailableReasonEnum::labels(),
            ],
            'update_route'  => $canEdit ? $this->route('grp.models.org.org_supplier_product.agent_details.update', $orgSupplierProduct, $viewingOrganisation) : null,
        ];
    }

    /**
     * @return array{value: int|null, source: string|null}
     */
    private function inherited(?int $product, ?int $family, ?int $supplier = null): array
    {
        return match (true) {
            $product !== null  => ['value' => $product, 'source' => 'product'],
            $family !== null   => ['value' => $family, 'source' => 'family'],
            $supplier !== null => ['value' => $supplier, 'source' => 'supplier'],
            default            => ['value' => null, 'source' => null],
        };
    }

    /**
     * @return array<string, array{value: mixed, source: string|null}>
     */
    private function packagingTerms(SupplierProduct $supplierProduct, ?SupplierStockFamily $familyAtSupplier): array
    {
        $terms = [];
        foreach ([...self::PAID_PACKAGING_TERMS, 'protective_materials', 'silica_gel_sachets'] as $term) {
            $own    = Arr::get($supplierProduct->packaging_terms, $term);
            $family = Arr::get($familyAtSupplier?->packaging_terms ?? [], $term);

            $terms[$term] = match (true) {
                filled($own)    => ['value' => $own, 'source' => 'product'],
                filled($family) => ['value' => $family, 'source' => 'family'],
                default         => ['value' => null, 'source' => null],
            };
        }

        return $terms;
    }

    /**
     * Trade unit dimensions are stored in metres whatever unit they are shown in.
     *
     * @return array{l: float, w: float, h: float}|null
     */
    private function unitDimensionsInCm(?TradeUnit $tradeUnit): ?array
    {
        $dimensions = $tradeUnit?->marketing_dimensions;
        if (!$dimensions || !(float) Arr::get($dimensions, 'l') || !(float) Arr::get($dimensions, 'w') || !(float) Arr::get($dimensions, 'h')) {
            return null;
        }

        return [
            'l' => round($dimensions['l'] * 100, 2),
            'w' => round($dimensions['w'] * 100, 2),
            'h' => round($dimensions['h'] * 100, 2),
        ];
    }

    /**
     * @return array<int, array{key: string, message: string}>
     */
    private function cbmWarnings(SupplierProduct $supplierProduct, ?TradeUnit $tradeUnit, ?float $cbm, ?int $unitsPerCarton): array
    {
        $warnings   = [];
        $dimensions = $supplierProduct->carton_dimensions;

        if ($cbm && $dimensions) {
            $fromDimensions = $dimensions['l'] * $dimensions['w'] * $dimensions['h'] / 1_000_000;
            if (abs($fromDimensions - $cbm) > $cbm * 0.1) {
                $warnings[] = [
                    'key'     => 'cbm_not_dimensions',
                    'message' => __('The CBM is :cbm m³ but the carton size gives :calculated m³.', ['cbm' => trimDecimalZeros($cbm), 'calculated' => trimDecimalZeros(round($fromDimensions, 4))]),
                ];
            }
        }

        $unitDimensions = $this->unitDimensionsInCm($tradeUnit);
        if ($cbm && $unitsPerCarton && $unitDimensions) {
            $unitVolume = $unitDimensions['l'] * $unitDimensions['w'] * $unitDimensions['h'] / 1_000_000;
            $ratio      = ($cbm / $unitsPerCarton) / $unitVolume;
            if ($ratio > 3 || $ratio < 0.8) {
                $warnings[] = [
                    'key'     => 'cbm_not_units',
                    'message' => __('Each unit takes :ratio× its own size in the carton (:units units in :cbm m³). Check the CBM and the units per carton.', [
                        'ratio' => trimDecimalZeros(round($ratio, 1)),
                        'units' => $unitsPerCarton,
                        'cbm'   => trimDecimalZeros($cbm),
                    ]),
                ];
            }
        }

        return $warnings;
    }

    /**
     * @return array{key: string, message: string}|null
     */
    private function weightWarning(SupplierProduct $supplierProduct, ?TradeUnit $tradeUnit, ?int $unitsPerCarton): ?array
    {
        if (!$supplierProduct->carton_weight || !$unitsPerCarton || !$tradeUnit?->gross_weight) {
            return null;
        }

        $unitsWeight = $unitsPerCarton * $tradeUnit->gross_weight;
        if ($supplierProduct->carton_weight >= $unitsWeight) {
            return null;
        }

        return [
            'key'     => 'carton_lighter_than_units',
            'message' => __('The carton weighs :carton g but :units units × :unit g = :expected g.', [
                'carton'   => $supplierProduct->carton_weight,
                'units'    => $unitsPerCarton,
                'unit'     => $tradeUnit->gross_weight,
                'expected' => $unitsWeight,
            ]),
        ];
    }

    /**
     * Other listings of the same SKO whose CBM is 10× or 100× this one: a decimal point slipped on one of them.
     *
     * @return array<int, array{code: string, supplier: string, cbm: float, factor: string}>
     */
    private function cbmSiblings(SupplierProduct $supplierProduct, ?float $cbm): array
    {
        if (!$cbm) {
            return [];
        }

        return SupplierProduct::query()
            ->whereHas('stocks', fn ($query) => $query->whereIn('stocks.id', $supplierProduct->stocks->pluck('id')))
            ->where('id', '!=', $supplierProduct->id)
            ->where('cbm', '>', 0)
            ->with('supplier')
            ->get()
            ->map(function (SupplierProduct $sibling) use ($cbm) {
                $ratio  = (float) $sibling->cbm / $cbm;
                $factor = collect([100, 10, 0.1, 0.01])->first(fn (float $factor) => abs($ratio - $factor) <= $factor * 0.02);

                return $factor ? [
                    'code'     => $sibling->code,
                    'supplier' => $sibling->supplier->code,
                    'cbm'      => (float) $sibling->cbm,
                    'factor'   => match ($factor) {
                        100     => __('100 times'),
                        10      => __('10 times'),
                        0.1     => __('a tenth of'),
                        default => __('a hundredth of'),
                    },
                ] : null;
            })
            ->filter()
            ->values()
            ->all();
    }

    private function route(string $name, OrgSupplierProduct $orgSupplierProduct, Organisation $viewingOrganisation): array
    {
        return [
            'name'       => $name,
            'parameters' => [
                'organisation'       => $viewingOrganisation->id,
                'orgSupplierProduct' => $orgSupplierProduct->id,
            ],
        ];
    }
}

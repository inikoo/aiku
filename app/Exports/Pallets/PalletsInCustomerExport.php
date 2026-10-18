<?php

namespace App\Exports\Pallets;

use App\Enums\Fulfilment\Pallet\PalletStatusEnum;
use App\Enums\UI\Fulfilment\FulfilmentCustomerPalletsTabsEnum;
use App\Models\Fulfilment\FulfilmentCustomer;
use App\Models\Fulfilment\Pallet;
use App\Models\Fulfilment\StoredItem;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class PalletsInCustomerExport implements FromQuery, WithHeadings, ShouldAutoSize, WithMapping
{
    use Exportable;

    public function __construct(
        protected FulfilmentCustomer $fulfilmentCustomer,
        protected string $tab = FulfilmentCustomerPalletsTabsEnum::ALL->value,
        protected bool $oneRowPerStoredItem = false
    ) {
    }

    public function query(): Builder
    {
        $query = Pallet::query()
            ->where('fulfilment_customer_id', $this->fulfilmentCustomer->id)
            ->leftJoin('locations', 'pallets.location_id', '=', 'locations.id')
            ->with(['storedItems:id,name,reference'])
            ->select('pallets.*', 'locations.code as location_code');

        if ($this->tab === FulfilmentCustomerPalletsTabsEnum::STORING->value) {
            $query->whereIn('pallets.status', [
                PalletStatusEnum::STORING->value,
                PalletStatusEnum::RETURNING->value,
            ]);
        } elseif ($this->tab === FulfilmentCustomerPalletsTabsEnum::INCOMING->value) {
            $query->whereIn('pallets.status', [PalletStatusEnum::IN_PROCESS->value]);
        } elseif ($this->tab === FulfilmentCustomerPalletsTabsEnum::INCIDENT->value) {
            $query->whereIn('pallets.status', [PalletStatusEnum::INCIDENT->value]);
        } elseif ($this->tab === FulfilmentCustomerPalletsTabsEnum::RETURNED->value) {
            $query->whereIn('pallets.status', [PalletStatusEnum::RETURNED->value]);
        }

        return $query->orderBy('pallets.reference');
    }

    public function map($row): array
    {
        /** @var Pallet $pallet */
        $pallet = $row;

        $palletColumns = [
            is_object($pallet->type) ? $pallet->type->value : (string) $pallet->type,
            (string) $pallet->reference,
            (string) $pallet->customer_reference,
            (string) ($pallet->location_code ?? ''),
        ];

        $storedItems = collect($pallet->storedItems ?? []);

        if ($this->oneRowPerStoredItem) {
            if ($storedItems->isEmpty()) {
                return [[...$palletColumns, '', '', '']];
            }

            return $storedItems
                ->map(fn (StoredItem $storedItem): array => [
                    ...$palletColumns,
                    (string) $storedItem->reference,
                    (string) $storedItem->name,
                    (string) (int) ($storedItem->pivot->quantity ?? 0),
                ])
                ->values()
                ->all();
        }

        return [
            ...$palletColumns,
            $storedItems
                ->map(fn (StoredItem $storedItem): string => sprintf('%s (%d)', $this->storedItemLabel($storedItem), (int) ($storedItem->pivot->quantity ?? 0)))
                ->implode('; '),
            (string) $storedItems->sum(fn (StoredItem $storedItem): int => (int) ($storedItem->pivot->quantity ?? 0)),
        ];
    }

    private function storedItemLabel(StoredItem $storedItem): string
    {
        $reference = trim((string) $storedItem->reference);
        $name      = trim((string) $storedItem->name);

        if ($name === '' || $name === $reference) {
            return $reference;
        }

        return $reference.' - '.$name;
    }

    public function headings(): array
    {
        $palletHeadings = [
            __('Type'),
            __('Reference'),
            __('Customer Reference'),
            __('Location Code'),
        ];

        if ($this->oneRowPerStoredItem) {
            return [
                ...$palletHeadings,
                __("Customer's SKO"),
                __("Customer's SKO Name"),
                __('Quantity'),
            ];
        }

        return [
            ...$palletHeadings,
            __("Customer's SKOs"),
            __("Customer's SKOs Quantity"),
        ];
    }
}

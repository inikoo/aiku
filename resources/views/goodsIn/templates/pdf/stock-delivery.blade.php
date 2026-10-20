<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Stock Delivery - {{ $stockDelivery->reference }}</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; color: #333; }
        .header { text-align: center; margin-bottom: 40px; }
        .header h1 { margin: 0; font-size: 26px; text-transform: uppercase; }
        .meta-info { width: 100%; margin-bottom: 25px; border-bottom: 2px solid #ddd; padding-bottom: 10px; }
        .meta-info td { padding: 5px 0; }
        table.items { width: 100%; border-collapse: collapse; margin-top: 10px; }
        table.items th, table.items td { border: 1px solid #ddd; padding: 8px 6px; text-align: left; }
        .text-right { text-align: right; }
        .note { margin-top: 4px; font-size: 11px; font-weight: bold; color: #92400e; }
        table.items th { background-color: #f4f4f4; text-transform: uppercase; font-size: 10px; }
        .text-center { text-align: center; }
        .tick-box { width: 24px; height: 24px; border: 1px solid #333; }
        .footer { margin-top: 40px; font-size: 12px; color: #777; text-align: center; font-style: italic; }
    </style>
</head>
<body>

    <div class="header">
        <h1>{{ __("Goods In") }}</h1>
    </div>

    <table class="meta-info">
        <tr>
            <td>
                <strong>{{ __("Reference") }}:</strong> {{ $stockDelivery->reference }}<br>
                <strong>{{ __("Supplier / Agent") }}:</strong> {{ $stockDelivery->parent_name }}<br>
                <strong>{{ __("Organisation") }}:</strong> {{ $stockDelivery->organisation->name }}
            </td>
            <td style="text-align: right;">
                <strong>{{ __("State") }}:</strong> {{ \App\Enums\GoodsIn\StockDelivery\StockDeliveryStateEnum::labels()[$stockDelivery->state->value] ?? $stockDelivery->state->value }}<br>
                <strong>{{ __("Date") }}:</strong> {{ $stockDelivery->date ? $stockDelivery->date->format('jS F, Y') : 'N/A' }}<br>
                <strong>{{ __("Date Printed") }}:</strong> {{ \Carbon\Carbon::now()->format('jS F, Y') }}
            </td>
        </tr>
    </table>

    <table class="items">
        <thead>
            <tr>
                <th>{{ __("Code") }}</th>
                <th>{{ __("Name") }}</th>
                <th class="text-right">{{ __("Units") }}</th>
                <th class="text-right">{{ __("SKO") }}</th>
                <th class="text-right">{{ __("Cartons") }}</th>
                @if($showCbm)
                    <th class="text-right">{{ __("CBM") }}</th>
                @endif
                <th class="text-center">{{ __("Checked") }}</th>
                <th class="text-center">{{ __("Placed") }}</th>
                <th class="text-center">{{ __("Tick") }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach($rows as $row)
                <tr>
                    <td style="white-space: nowrap">{{ $row['code'] ?? __('Unknown Code') }}</td>
                    <td>
                        {{ $row['name'] ?? __('Unknown Name') }}
                        @if($row['packed_inside'])
                            <div class="note">{{ __('Packed inside :code', ['code' => $row['packed_inside']]) }}</div>
                        @endif
                        @if($row['contains'])
                            <div class="note">{{ __('Contains: :codes', ['codes' => $row['contains']]) }}</div>
                        @endif
                    </td>
                    <td class="text-right">{{ number_format($row['units'], 0) }}</td>
                    <td class="text-right">{{ rtrim(rtrim(number_format($row['skos'], 2), '0'), '.') }}</td>
                    <td class="text-right">{{ $row['cartons'] === null ? '-' : rtrim(rtrim(number_format($row['cartons'], 2), '0'), '.') }}</td>
                    @if($showCbm)
                        <td class="text-right">{{ $row['cbm'] === null ? '-' : rtrim(rtrim(number_format($row['cbm'], 3), '0'), '.') }}</td>
                    @endif
                    <td class="text-center">{{ number_format($row['checked'], 0) }}</td>
                    <td class="text-center">{{ number_format($row['placed'], 0) }}</td>
                    <td class="text-center"><div class="tick-box"></div></td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
    </div>

</body>
</html>

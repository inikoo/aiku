{{--
    * Author: Vika Aqordi
    * Created on: 2026-10-20 11:25
    * Github: https://github.com/aqordeon
    * Copyright: 2026
--}}
@php($number = fn ($value, int $decimals = 3) => rtrim(rtrim(number_format((float)$value, $decimals, '.', ''), '0'), '.'))
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <title>{{ __('Packing instructions') }} {{ $purchaseOrder->reference }}</title>
    <style>
        body { font-family: sans-serif; font-size: 10pt; color: #222; }
        table { border-collapse: collapse; }
        .head { width: 100%; }
        .head td { padding: 3px 0; vertical-align: bottom; }
        .head .label { width: 34mm; font-weight: bold; }
        .head .blank { border-bottom: 1px dotted #555; width: 60mm; }
        .head .right { text-align: right; }
        .sheet { width: 100%; margin-top: 8px; }
        .sheet th, .sheet td { border: 1px solid #222; padding: 6px; vertical-align: middle; text-align: center; }
        .sheet th { background: #e6e6e6; font-size: 9pt; }
        .sheet .big { font-size: 13pt; font-weight: bold; }
        .sheet .left { text-align: left; }
        .sheet .muted { color: #666; font-size: 8.5pt; }
        .sheet .handwrite { height: 14mm; }
        .sheet .known { font-size: 9pt; vertical-align: top; text-align: left; }
        .steps { margin: 0; padding-left: 14px; text-align: left; list-style-type: disc; }
        .steps li { padding: 1px 0; }
        .none { color: #777; font-style: italic; }
        .inside { width: 100%; }
        .sheet .inside td { border: none; padding: 0; vertical-align: top; }
        .sheet .inside .steps-cell { text-align: left; }
        .sheet .inside .photo-cell { width: 46mm; text-align: center; padding-left: 6px; }
    </style>
</head>
<body>
@foreach($products as $product)
    <table class="head">
        <tr>
            <td class="label">{{ __('Date') }}</td>
            <td class="blank"></td>
            <td class="right"><b>{{ mb_strtoupper(__('Supplier')) }}:</b> {{ $product['supplier'] }}</td>
        </tr>
        <tr>
            <td class="label">{{ __('Packer name') }}</td>
            <td class="blank"></td>
            <td class="right" style="font-size: 8.5pt; color: #666;">{{ __('Printed') }}: {{ $printedAt }}</td>
        </tr>
        <tr>
            <td class="label">{{ __('Purchase order') }}</td>
            <td colspan="2">{{ $purchaseOrder->reference }}</td>
        </tr>
        <tr>
            <td class="label">{{ __('Organisation') }}</td>
            <td colspan="2">{{ $organisation }}</td>
        </tr>
        <tr>
            <td class="label">{{ __('Carton barcode') }}</td>
            <td colspan="2">
                @if($product['barcode'])
                    <barcode code="{{ $product['barcode'] }}" type="{{ $product['barcode_type'] }}" height="0.6" />
                    <span style="font-size: 11pt; font-weight: bold;">{{ $product['barcode'] }}</span>
                @else
                    <span class="none">{{ __('No carton barcode') }}</span>
                @endif
            </td>
        </tr>
    </table>

    <table class="sheet">
        <tr>
            <th colspan="2">{{ __('Product code') }}</th>
            <th colspan="2">{{ __('Product name') }}</th>
            <th style="width: 26%;">{{ __('Product image') }}</th>
        </tr>
        <tr>
            <td colspan="2">
                <div style="font-size: 13pt; font-weight: bold;">{{ $product['code'] }}</div>
                @if($product['supplier_code'])
                    <div class="muted">{{ __('Supplier code') }}: {{ $product['supplier_code'] }}</div>
                @endif
            </td>
            <td colspan="2">{{ $product['name'] }}</td>
            <td rowspan="3">
                @if($product['image'])
                    <img src="{{ $product['image'] }}" style="max-width: 42mm; max-height: 42mm;" alt="">
                @endif
            </td>
        </tr>
        <tr>
            <th>{{ __('Units ordered') }}</th>
            <th>{{ __('Units per carton') }}</th>
            <th>{{ __('Cartons') }}</th>
            <th>{{ __('Weight') }}</th>
        </tr>
        <tr>
            <td class="big">{{ $number($product['units']) }}</td>
            <td class="big">{{ $number($product['units_per_carton']) }}</td>
            @if($product['remainder'] > 0)
                <td>
                    <div style="font-size: 13pt; font-weight: bold;">{{ $product['full_cartons'] }} + {{ $number($product['remainder']) }} pcs</div>
                    <div class="muted">{{ __(':cartons cartons', ['cartons' => $product['cartons']]) }}</div>
                </td>
            @else
                <td class="big">{{ $product['full_cartons'] }}</td>
            @endif
            <td class="left">
                <div>{{ __('Unit') }}: <b>{{ $product['unit_weight_kg'] !== null ? $number($product['unit_weight_kg']).' kg' : '—' }}</b></div>
                <div>{{ __('Packing per carton') }}: <b>{{ $product['carton_tare_kg'] !== null ? $number($product['carton_tare_kg']).' kg' : '—' }}</b></div>
            </td>
        </tr>
        <tr>
            <th colspan="5">{{ mb_strtoupper(__('Packing instructions')) }}</th>
        </tr>
        <tr>
            <td colspan="5" class="left">
                <table class="inside">
                    <tr>
                        <td class="steps-cell">
                            @if($product['instructions'])
                                <ul class="steps">
                                    @foreach($product['instructions'] as $step)
                                        <li>{{ $step }}</li>
                                    @endforeach
                                </ul>
                            @else
                                <span class="none">{{ __('No packing instructions') }}</span>
                            @endif
                            @if($product['materials'])
                                <div style="margin-top: 6px;"><b>{{ __('Packing materials') }}:</b> {{ implode(', ', $product['materials']) }}</div>
                            @endif
                        </td>
                        @if($product['opened_carton'])
                            <td class="photo-cell">
                                <img src="{{ $product['opened_carton'] }}" style="max-width: 42mm; max-height: 42mm;" alt="">
                                <div class="muted">{{ __('Opened carton') }}</div>
                            </td>
                        @endif
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <th colspan="2">{{ __('Carton size (cm)') }}</th>
            <th>{{ __('Gross weight (kg)') }}</th>
            <th colspan="2">{{ __('Notes') }}</th>
        </tr>
        <tr>
            <td colspan="2" class="handwrite known">{{ $product['carton_size_cm'] }}</td>
            <td class="handwrite known">{{ $product['gross_weight_kg'] !== null ? $number($product['gross_weight_kg'], 2) : '' }}</td>
            <td colspan="2" class="handwrite"></td>
        </tr>
    </table>

    @if(!$loop->last)
        <pagebreak />
    @endif
@endforeach
@if($products->isEmpty())
    <p class="none">{{ __('This purchase order has no products to pack') }}</p>
@endif
</body>
</html>

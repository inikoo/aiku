{{--
    * Author: Vika Aqordi
    * Created on: 2026-10-20 10:20
    * Github: https://github.com/aqordeon
    * Copyright: 2026
--}}
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <title>{{ __('Packing instruction') }} {{ $product->code }}</title>
    <style>
        body { font-family: sans-serif; font-size: 10pt; color: #222; }
        table { border-collapse: collapse; }
        .heading { font-size: 14pt; font-weight: bold; }
        .sub { font-size: 9pt; color: #555; margin-top: 2px; }
        .facts { width: 100%; margin-top: 14px; }
        .facts td { padding: 3px 6px; border: 1px solid #bbb; font-size: 9pt; }
        .facts .label { background: #f0f0f0; width: 25%; }
        .block { margin-top: 16px; font-size: 11pt; font-weight: bold; }
        .lines { width: 100%; margin-top: 4px; }
        .lines th { border: 1px solid #222; background: #e6e6e6; font-size: 9pt; padding: 4px; text-align: left; }
        .lines td { border: 1px solid #222; padding: 6px; vertical-align: top; }
        .lines .qty { width: 60px; text-align: right; font-size: 13pt; font-weight: bold; }
        .lines .weight { width: 70px; text-align: right; }
        .names div { line-height: 1.35; }
        .muted { color: #777; font-size: 8.5pt; }
        .none { color: #777; font-style: italic; padding: 4px 0; }
        .photo { margin-top: 16px; }
    </style>
</head>
<body>
    <div class="heading">{{ mb_strtoupper(__('Packing instruction')) }} · {{ $product->code }}</div>
    <div class="sub">{{ $product->name }}@if($product->supplier) · {{ $product->supplier->name }}@endif</div>
    <div class="sub">{{ __('Version') }} {{ $spec->version }} · {{ __('Effective from') }} {{ $spec->effective_from->format('d/m/Y') }}</div>

    <table class="facts">
        <tr>
            <td class="label">{{ __('Units per pack') }}</td>
            <td>{{ $product->units_per_pack ?? '—' }}</td>
            <td class="label">{{ __('Units per carton') }}</td>
            <td>{{ $product->units_per_carton ?? '—' }}</td>
        </tr>
        <tr>
            <td class="label">{{ __('Carton (L × W × H)') }}</td>
            <td>
                @if($spec->carton_length_mm)
                    {{ $spec->carton_length_mm / 10 }} × {{ $spec->carton_width_mm / 10 }} × {{ $spec->carton_height_mm / 10 }} cm
                @else
                    —
                @endif
            </td>
            <td class="label">{{ __('Carton gross weight') }}</td>
            <td>{{ $spec->declared_carton_gross_weight_g ? number_format($spec->declared_carton_gross_weight_g / 1000, 2).' kg' : '—' }}</td>
        </tr>
    </table>

    @foreach($blocks as $block)
        <div class="block">{{ $block['label'] }}</div>
        @if($block['lines']->isEmpty())
            <div class="none">{{ __('Nothing') }}</div>
        @else
            <table class="lines">
                <tr>
                    <th class="qty">{{ __('Qty') }}</th>
                    <th>{{ __('Material') }}</th>
                    <th class="weight">{{ __('g each') }}</th>
                </tr>
                @foreach($block['lines'] as $line)
                    <tr>
                        <td class="qty">{{ rtrim(rtrim(number_format((float)$line->quantity, 3, '.', ''), '0'), '.') }}</td>
                        <td class="names">
                            <div>{{ $line->packagingComponent->dropdownLabel('en') }}</div>
                            <div>{{ $line->packagingComponent->dropdownLabel('zh') }}</div>
                            <div>{{ $line->packagingComponent->dropdownLabel('id') }}</div>
                            @if($line->description)
                                <div class="muted">{{ $line->description }}</div>
                            @endif
                        </td>
                        <td class="weight">{{ $line->weight_g_each !== null ? rtrim(rtrim(number_format((float)$line->weight_g_each, 3, '.', ''), '0'), '.') : '—' }}</td>
                    </tr>
                @endforeach
            </table>
        @endif
    @endforeach

    @if($photo)
        <div class="photo">
            <div class="block">{{ __('Opened carton') }}</div>
            <img src="{{ $photo }}" style="max-width: 120mm; max-height: 90mm;" alt="">
        </div>
    @endif
</body>
</html>

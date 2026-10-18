{{--
    * Author: Vika Aqordi
    * Created on: 2026-10-16 10:48
    * Github: https://github.com/aqordeon
    * Copyright: 2026
--}}
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <title>{{ __('Receipt note') }} {{ $receipt->reference }}</title>
    <style>
        body { font-family: sans-serif; font-size: 10pt; color: #222; }
        .header { width: 100%; }
        .header td { vertical-align: middle; }
        .header .side { width: 32mm; }
        .heading { text-align: center; }
        table { border-collapse: collapse; }
        .info { width: 100%; margin-top: 40px; }
        .info td { padding: 1px 0; vertical-align: top; }
        .info .label { width: 110px; }
        .info .colon { width: 14px; }
        .info .date { text-align: right; }
        .lines { width: 100%; margin-top: 18px; }
        .lines th { border: 1px solid #222; background: #e6e6e6; font-weight: bold; font-size: 9pt; padding: 4px; text-align: center; }
        .lines td { border: 1px solid #222; padding: 9px 6px; vertical-align: middle; }
        .lines .no { width: 40px; text-align: center; }
        .lines .number { text-align: right; }
        .lines .unit { width: 40px; text-align: center; }
        .lines .code { font-weight: bold; }
        .lines .remarks { font-size: 9pt; width: 170px; }
        .lines .ordered { width: 70px; }
        .lines .received { width: 50px; }
        .lines .po { width: 90px; font-size: 9pt; }
        .lines .rejected { width: 50px; }
        .signatures { width: 100%; margin-top: 18px; }
        .signatures td { width: 50%; text-align: center; vertical-align: top; }
        .signatures .line { margin: 70px auto 0; width: 60%; border-top: 1px solid #222; }
    </style>
</head>
<body>
    <table class="header">
        <tr>
            <td class="side">
                @if($logo)
                    <img src="{{ $logo }}" style="height: 18mm; width: auto;" alt="">
                @endif
            </td>
            <td class="heading">
                <div style="font-size: 13pt;">{{ mb_strtoupper(__('Receipt note')) }}</div>
                <div style="font-size: 13pt; margin-top: 4px;">{{ mb_strtoupper($agent->name) }}</div>
                @if($agent->address)
                    <div style="font-size: 9.5pt; margin-top: 4px;">{{ str_replace("\n", ', ', $agent->address->formatted_address) }}</div>
                @endif
            </td>
            <td class="side"></td>
        </tr>
    </table>

    <table class="info">
        <tr>
            <td class="label">{{ mb_strtoupper(__('Supplier name')) }}</td>
            <td class="colon">:</td>
            <td>{{ $supplierName }}</td>
            <td class="date">{{ mb_strtoupper(__('Date')) }} : {{ $receipt->received_at->format('d/m/Y') }}</td>
        </tr>
        <tr>
            <td class="label">{{ mb_strtoupper(__('Receipt no.')) }}</td>
            <td class="colon">:</td>
            <td colspan="2">{{ $receipt->reference }}</td>
        </tr>
        @if($receipt->supplier_delivery_note)
            <tr>
                <td class="label">{{ mb_strtoupper(__('Delivery note')) }}</td>
                <td class="colon">:</td>
                <td colspan="2">{{ $receipt->supplier_delivery_note }}</td>
            </tr>
        @endif
    </table>

    <table class="lines">
        <thead>
            <tr>
                <th class="no">{{ mb_strtoupper(__('No.')) }}</th>
                <th class="po">{{ mb_strtoupper(__('PO number')) }}</th>
                <th>{{ mb_strtoupper(__('Product name/code')) }}</th>
                <th class="ordered">{{ mb_strtoupper(__('Total order')) }}</th>
                <th colspan="2">{{ mb_strtoupper(__('Qty received')) }}</th>
                <th class="rejected">{{ mb_strtoupper(__('Rejected')) }}</th>
                <th class="remarks">{{ mb_strtoupper(__('Remarks')) }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach($lines as $index => $line)
                <tr>
                    <td class="no">{{ $index + 1 }}</td>
                    <td class="po">{{ $line['purchase_order'] }}</td>
                    <td><span class="code">{{ $line['code'] }}</span> {{ $line['name'] }}</td>
                    <td class="number">{{ trimDecimalZeros($line['ordered']) }}</td>
                    <td class="number received">{{ trimDecimalZeros($line['received']) }}</td>
                    <td class="unit">{{ __('pcs') }}</td>
                    <td class="number rejected">{{ $line['rejected'] === null ? '' : trimDecimalZeros($line['rejected']) }}</td>
                    <td class="remarks">{!! nl2br(e($line['remarks'])) !!}</td>
                </tr>
            @endforeach
            @for($index = $lines->count(); $index < max(5, $lines->count() + 2); $index++)
                <tr>
                    <td class="no">{{ $index + 1 }}</td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td class="unit"></td>
                    <td></td>
                    <td></td>
                </tr>
            @endfor
        </tbody>
    </table>

    <table class="signatures">
        <tr>
            <td>
                {{ mb_strtoupper(__('Sender')) }}
                <div class="line"></div>
            </td>
            <td>
                {{ mb_strtoupper(__('Receiver')) }}
                <div class="line"></div>
            </td>
        </tr>
    </table>
</body>
</html>

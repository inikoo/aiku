@php
    $hasWarnings = (bool) array_filter($label['warnings']);
@endphp

<table border="0" cellpadding="0" cellspacing="0" style="width: 100%; font-family: dejavusans; color: #000;">
    <tr>
        <td colspan="2" style="border-bottom: 0.3mm solid #000; padding-bottom: 0.6mm; font-size: 11pt; line-height: 1;">
            <span style="font-size: 11pt; font-weight: bold;">{{ $label['code'] }}</span>&nbsp;
            <span style="font-size: 7pt; font-weight: bold;">{{ $label['name'] }}</span>
        </td>
    </tr>
    <tr>
        <td colspan="2" style="border-bottom: 0.1mm solid #999; padding: 0.6mm 0; font-size: 4.3pt; line-height: 1.2;">
            @foreach ($label['names'] as $language => $name)<b>{{ strtoupper($language) }}</b> {{ $name }}@if (!$loop->last) · @endif @endforeach
        </td>
    </tr>
    <tr>
        <td colspan="2" style="padding-top: 0.8mm; font-size: 5.5pt; font-weight: bold;">{{ $label['made_in'] }}</td>
    </tr>
    @if ($label['material'])
        <tr>
            <td colspan="2" style="font-size: 5pt;">Material: {{ \Illuminate\Support\Str::limit($label['material'], 160) }}</td>
        </tr>
    @endif
    <tr>
        <td colspan="2" style="font-size: 5.5pt;">Batch / Lote / Šarža: <span style="font-size: 8pt; font-weight: bold;">{{ $label['batch'] }}</span></td>
    </tr>
    <tr>
        <td width="66%" valign="top" style="padding: 0.8mm 2mm 0 0; font-size: 4pt; line-height: 1.2;">
            @if ($hasWarnings)
                ⚠ @foreach (array_filter($label['warnings']) as $language => $warning)<b>{{ strtoupper($language) }}</b> {{ $warning }}@if (!$loop->last) · @endif @endforeach
            @endif
        </td>
        <td width="34%" align="right" valign="bottom" style="padding: 0.8mm 0;">
            <barcode code="{{ $label['unit_ean13'] }}" type="EAN13" size="0.8" height="{{ $hasWarnings ? 0.6 : 0.7 }}"/>
        </td>
    </tr>
    @include('labels.templates.pdf.org_stock.unified.operators', ['columns' => 2, 'size' => 4.1, 'withRole' => true])
</table>

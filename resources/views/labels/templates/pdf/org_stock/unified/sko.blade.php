<table border="0" cellpadding="0" cellspacing="0" style="width: 100%; font-family: dejavusans; color: #000;">
    <tr>
        <td colspan="2">
            <table border="0" cellpadding="0" cellspacing="0">
                <tr>
                    <td style="background-color: #000; color: #fff; font-weight: bold; font-size: 15pt; line-height: 1; padding: 1.2mm 2.2mm;">{{ $label['code'] }}</td>
                    <td style="padding-left: 2.2mm; font-size: 5.5pt; line-height: 1.1;">
                        <span style="font-size: 12pt; font-weight: bold;">PACK OF {{ $label['units_per_pack'] }}</span><br/>
                        {{ $label['units_per_pack'] }} units · {{ $label['units_per_pack'] }} unidades · {{ $label['units_per_pack'] }} kusov
                    </td>
                </tr>
            </table>
        </td>
    </tr>
    <tr>
        <td colspan="2" style="padding-top: 0.8mm; font-size: 8pt; font-weight: bold;">{{ $label['name'] }}</td>
    </tr>
    <tr>
        <td colspan="2" style="font-size: 5pt;"><b>ES</b> {{ $label['names']['es'] }} · <b>SK</b> {{ $label['names']['sk'] }}</td>
    </tr>
    <tr>
        <td colspan="2" style="font-size: 5pt; color: #555;">Importers: {{ implode(' · ', array_column($label['operators'], 'short')) }}</td>
    </tr>
    <tr>
        <td colspan="2" align="center" style="padding-top: 1.5mm;">
            <img src="{{ $barcode['uri'] }}" style="width: {{ $barcode['width'] }}; height: {{ $barcode['height'] }};"/>
        </td>
    </tr>
    <tr>
        <td colspan="2" align="center" style="font-size: 7.5pt; padding-bottom: 0.8mm;">{{ $label['hri'] }}</td>
    </tr>
    <tr>
        <td style="border-top: 0.2mm solid #000; padding-top: 0.6mm; font-size: 5.5pt; font-weight: bold;">Contains {{ $label['units_per_pack'] }} × EAN {{ $label['unit_ean13'] }}</td>
        <td align="right" style="border-top: 0.2mm solid #000; padding-top: 0.6mm; font-size: 5.5pt;">Batch {{ $label['batch'] }} · {{ \Illuminate\Support\Str::before($label['made_in'], ' · ') }}</td>
    </tr>
</table>

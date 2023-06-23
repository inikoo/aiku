<!--
 About:
 Author: Raul Perusquia <raul@inikoo.com>
 Created: 14 March 2019 at 14:13:56 GMT+8, Ubud, Bali, Indonesia
 Refurbished:  01 May 2020  21:34::26  +0800, Kuala Lumpur, Malaysialabe
 Copyright (c) 2018, Inikoo

 Version 3
-->

<style>
    .top td {
        font-size: {{ $labelFontSize }}mm;
        color: #000;
        text-align: center;
        vertical-align: bottom;
        padding: 0 5px 2px 5px;
        font-family: Arial, "Helvetica Neue", Helvetica, sans-serif;
    }

    .labels td {
        font-size: {{ $labelFontSize }}mm;
        color: #000;
        text-align: center;
        vertical-align: bottom;
        padding: 4px 5px 0 5px;
        border-top: .1mm solid #000;
        font-family: Arial, "Helvetica Neue", Helvetica, sans-serif;
    }

    .data td {
        text-align: center;
        vertical-align: bottom;
        padding: 1px 5px 4px 5px;
        border-bottom: .1mm solid #000;
        font-family: Arial, "Helvetica Neue", Helvetica, sans-serif;
    }
</style>
<table style="width: 100%; border-collapse: collapse;">
    <tr class="top" >
        <td colspan="4"> Commercialised by Ancient Wisdom s.r.o.</td>
    </tr>
    <tr class="labels">
        <td >Reference
        </td>
        <td >Units per pack</td>
        <td >
            Packs per carton
        </td>
        <td >
            Units per carton
        </td>
    </tr>
    <tr class="data">
        <td>
            <b>GSFB-05</b>
        </td>
        <td>
            <b>1</b>
        </td>
        <td>
            <b>18</b>
        </td>
        <td>
            <b>18</b>
        </td>
    </tr>

    <tr class="labels">
        <td colspan="4">
            Unit description
        </td>
    </tr>

    <tr class="data">
        <td colspan="4">
            <b>Soap Flower Gift Bouquet - 13 Pink Roses - Hear</b>
        </td>
    </tr>

    <tr class="labels">
        <td colspan="4">Materials</td>
    </tr>

    <tr class="data">
        <td colspan="4" style="font-size:{{ $contentFontSize }}mm">
            Zea Mays (Corn Starch) , Polyvinyl Alcohol , Aqua , Sodium Dodecyl Sulphate ,
            Cocamide DEA , Propylene Glycol , Paraffinum Liquidum , Parfum ,
            Methylisothiazolinone , (+/- CI 16035 , CI 19140 , CI 42090 , CI 18050 , CI 16255 , CI
            45430 , CI 15985)
        </td>
    </tr>

    <tr class="labels">
        <td >
            Batch code
        </td>
        <td >
            Net weight
        </td>
        <td >
            Gross weight
        </td>
        <td >
            Origin
        </td>
    </tr>

    <tr class="data">
        <td >
            <b>AW202306</b>
        </td>
        <td  >
            <b>6.7Kg</b>
        </td>
        <td  >
            <b>6Kg</b>
        </td>
        <td  >
            <b>CHN</b>
        </td>
    </tr>

    <tr>
        <td colspan="4" style="text-align: center;"><barcode size="{{ $barcodeSize }}" code="2837287328" type="C128B" />

        </td>
    </tr>

    <tr class="labels" >
        <td colspan="4" style="padding-top: 2px">5056422951647C</td>
    </tr>

    <tr class="data">
        <td colspan="4" style="font-size:{{ $contentFontSize }}mm">
            this is custom text
        </td>
    </tr>
</table>

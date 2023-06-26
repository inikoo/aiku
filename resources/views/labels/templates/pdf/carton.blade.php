{{--
* Author: Artha <artha@aw-advantage.com>
* Created: Fri, 23 Jun 2023 11:43:59 Central Indonesia Time, Sanur, Bali, Indonesia
* Copyright (c) 2023, Raul A Perusquia Flores
*/
--}}

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
        font-size: {{ $contentFontSize }}mm;
        text-align: center;
        vertical-align: bottom;
        padding: 1px 5px 4px 5px;
        border-bottom: .1mm solid #000;
        font-family: Arial, "Helvetica Neue", Helvetica, sans-serif;
    }
</style>
<table style="width: 100%; border-collapse: collapse;" border="0">
    <tr class="top">
        <td colspan="@if($withImage) 5 @else 4 @endif"> Commercialised by Ancient Wisdom s.r.o.</td>
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
        @if($withImage)
        <td rowspan="4" style="text-align: center;vertical-align: middle;" valign="middle">
            <img style="vertical-align: middle;max-height: 25mm" src="https://mm.widyatama.ac.id/wp-content/uploads/2020/08/dummy-profile-pic-male1.jpg" width="{{ $imageSize }}mm"/>
        <td>
        @endif
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

    @if($material)
    <tr class="labels">
        <td colspan="4">Materials</td>
    </tr>

    <tr class="data">
        <td colspan="@if($withImage) 5 @else 4 @endif" style="font-size:{{ $contentFontSize }}mm">
            {{ $material }}
        </td>
    </tr>
    @endif

    <tr class="labels">
        <td @if($withImage) colspan="2" @endif>
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
        <td @if($withImage) colspan="2" @endif>
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
        <td colspan="@if($withImage) 5 @else 4 @endif" style="text-align: center;"><barcode size="{{ $barcodeSize }}" code="2837287328" type="C128B" />

        </td>
    </tr>

    <tr class="labels" >
        <td colspan="@if($withImage) 5 @else 4 @endif" style="padding-top: 2px">5056422951647C</td>
    </tr>

    @if($customText)
    <tr class="data">
        <td colspan="@if($withImage) 5 @else 4 @endif" style="font-size:{{ $contentFontSize }}mm">
            $customText
        </td>
    </tr>
    @endif
</table>

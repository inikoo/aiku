{{--
* Author: Artha <artha@aw-advantage.com>
* Created: Fri, 23 Jun 2023 11:43:59 Central Indonesia Time, Sanur, Bali, Indonesia
* Copyright (c) 2023, Raul A Perusquia Flores
*/
--}}

<html>
<head>
    <title>{{ $filename }}</title>
    <style>
        body {
            font-family: sans-serif;
            font-size: 10pt;
        }

        p {
            margin: 0pt;
        }

        h1 {
            font-size: 14pt
        }

        td {
            vertical-align: top;
        }

        .items td {
            border-left: 0.1mm solid #000000;
            border-right: 0.1mm solid #000000;
            border-bottom: 0.1mm solid #cfcfcf;
            padding-bottom: 4px;
            padding-top: 5px;
        }


        .items tbody.out_of_stock td {
            color: #777;
            font-style: italic
        }

        .items tbody.totals td {
            text-align: right;
            border: 0.1mm solid #222;
        }

        .items tr.total_net td {
            border-top: 0.3mm solid #000;
        }

        .items tr.total td {
            border-top: 0.3mm solid #000;
            border-bottom: 0.3mm solid #000;
        }

        .items tr.last td {

            border-bottom: 0.1mm solid #000000;
        }

        table thead td, table tr.title td {
            background-color: #EEEEEE;
            text-align: center;
            border: 0.1mm solid #000000;
        }

        .items td.blanktotal {
            background-color: #FFFFFF;
            border: 0mm none #000000;
            border-top: 0.1mm solid #000000;
            border-right: 0.1mm solid #000000;
        }


        div.inline {
            float: left;
        }

        .clearBoth {
            clear: both;
        }

        .hide {
            display: none
        }
    </style>
</head>
<body>
<htmlpageheader name="myheader">
    <br><br>
    <table width="100%" style="font-size: 9pt;">
        <tr>
            <td style="width:250px;padding-left:10px;">
                AW-AromaticsAW
                <div style="font-size:7pt">
                    Aromatics Ltd
                </div>
                <div style="font-size:7pt">
                    Unit 15
                </div>
                <div style="font-size:7pt">
                    Parkwood Business Park
                </div>
                <div style="font-size:7pt">
                    Parkwood Road
                </div>
                <div style="font-size:7pt">
                    Sheffield S3 8AL
                </div>
                <div style="font-size:7pt">
                    www.aw-aromatics.com
                </div>
            </td>

            <td style="text-align: right;">Delivery Note No.<br/>
                <b>{{ $delivery->reference }}</b>
            </td>

        </tr>
    </table>
</htmlpageheader>

<sethtmlpageheader name="myheader" value="on" show-this-page="1"/>
<sethtmlpagefooter name="myfooter" value="on"/>

<br><br><br><br><br>

<table width="100%" style="margin-top: 40px">
    <tr>
        <td>
            <h1>
                Delivery Note
            </h1>
        </td>
        <td style="text-align: right">
            <div>
                Dispatch Date: <b>{{ $delivery->created_at->format('j F Y') }}</b>
            </div>
        </td>
    </tr>
</table>
<table width="100%" style="font-family: sans-serif; margin-top: 20px" cellpadding="0">
    <tr>
        <td width="50%" style="vertical-align:bottom;border: 0mm solid #888888;">
            <div>
                <div>
                    Customer: <b>{{ $customer->name }}</b>
                    ({{ $customer->reference }})
                </div>
                @if($customer->phone)
                    <div>
                        <span class="address_label">Phone:</span> <span class="address_value">{{ $customer->phone }}</span>
                    </div>
                @endif
            </div>
        </td>
        <td width="50%" style="vertical-align:bottom;border: 0mm solid #888888;text-align: right">
            <div style="text-align:right;">
                Parcels: <b>{{ $delivery->pallets()->count() }} box</b>
            </div>
            <div style="text-align: right">Weight: <b>2Kg</b></div>

        </td>
    </tr>
</table>
<table width="100%" style="font-family: sans-serif;" cellpadding="10">
    <tr>
        <td width="45%" style="border: 0.1mm solid #888888;">
            <span style="font-size: 7pt; color: #555555; font-family: sans-serif;">Delivery address:</span>
            <div>
                {{ $customer->addresses[0]['address_line_1'] }}
            </div>
            <div>
                {{ $customer->addresses[0]['address_line'] }}
            </div>
            <div>
                {{ $customer->addresses[0]['administrative_area'] }}
            </div>
            <div>
                {{ $customer->addresses[0]->country['name'] }}
            </div>
        </td>
        <td width="10%">&nbsp;</td>
        <td width="45%">&nbsp;</td>
    </tr>
</table>
<br>

<table class="items" width="100%" style="font-size: 9pt; border-collapse: collapse;" cellpadding="8">
    <thead>
    <tr>
        <td style="width:20%; text-align:left">Code</td>

        <td style="width:50%; text-align:left">Description</td>

        <td style="text-align:right">Dispatched</td>
    </tr>
    </thead>
    <tbody>

    @foreach($delivery->pallets as $pallet)
    <tr class="@if($loop->last) last @endif">
        <td style="text-align:left">{{ $pallet->reference }}</td>

        <td style="text-align:left">{{ $pallet->notes }}</td>

        <td style="text-align:right">1</td>
    </tr>
    @endforeach

    </tbody>

</table>

<br>
<br>
<br>

<div style="text-align: center; font-style: italic;">
    The exporter of products covered by this document GB356317102000 declares that, unless otherwise clearly
    indicated, these products are of UK preferential origin. Such products are covered by the The EU-UK Trade and
    Cooperation Agreement and MUST be regarded with a preferential 0% tariff code.
</div>

<br>

<div style="text-align: center; font-style: italic;">
    EORI: GB356317102000 XI EORI: XI356317102000 Thank you for your order. Bank Details:
    Beneficiary: AW Aromatics Ltd Bank: HSBC UK Bank PLC Address: Carmel House, 49 - 63 Fargate,S1 2HD, Sheffield UK
    Account Number: 70861278 Bank Code: 404157 Swift: HBUKGB4B IBAN: GB15HBUK40415770861278
</div>

<htmlpagefooter name="myfooter">
    <div
        style="border-top: 1px solid #000000; font-size: 9pt; text-align: center; padding-top: 3mm; margin-top: 120px"></div>
    <table width="100%">
        <tr>
        <tr>
            <td width="33%" style="color:#000;text-align: left;">
                <small>AW Aromatics Limited<br> VAT Number:
                    <b>GB356317102</b>
                    <br>
                    Registration Number: 1279117</small>
            </td>
            <td width="33%" style="color:#000;text-align: center">
                Page 1 of 1
            </td>
            <td width="34%" style="text-align: right;">
                <small>00441144384914<br>
                    sales@aw-aromatics.com
                </small>
            </td>
        </tr>
    </table>
    <br><br>
</htmlpagefooter>
</body>
</html>

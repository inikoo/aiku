<html>
<head>
    <style>
        body {font-family: sans-serif;
            font-size: 8pt;
        }
        p {    margin: 0pt;
        }
        td { vertical-align: top; }
        .items td {
            border-left: 0.1mm solid #000000;
            border-right: 0.1mm solid #000000;
            border-bottom: 0.1mm solid #b0b0b0;
        }


        table thead td { background-color: #EEEEEE;
            text-align: center;
            border: 0.1mm solid #000000;
        }

        .items tr.last td {

            border-bottom: 0.1mm solid #000000;
        }

        .items tr.even  td {

            background-color: #FAFAFA;
        }

        .items tr.multiple_partsx td{
            border-top: 0.5mm solid #000000;
            border-bottom: 0.5mm solid #000000;

        }

        .items td.multiple_parts {
            background-color: #FCFCFC;

            border: 0.5mm solid #000000;
        }

        .items td.blanktotal {
            background-color: #FFFFFF;
            border: 0mm none #000000;
            border-top: 0.1mm solid #000000;
            border-right: 0.1mm solid #000000;
        }
        .items td.totals {
            text-align: right;
            border: 0.1mm solid #000000;
        }

        div.inline { float:left; }

        div.clearBoth { clear:both; }


        hr {
            border-top: 0.1mm solid #000000;
            height:1px;

        }
        #order_pick_aid_data {width:100%; border-spacing:0; border-collapse:collapse;}
        #order_pick_aid_data tr{border-bottom: 0.1mm solid #000000}
        #order_pick_aid_data td{padding-bottom:4px;padding-top:5px}
        #order_pick_aid_data td.label{border-bottom: 0.1mm solid #000000}
        #order_pick_aid_data td.to_fill{border-bottom: 0.1mm solid #000000;}

        .hide{display: none}

        .address_label{font-size: 7pt; color: #555555; font-family: sans-serif;}

        .address_value{font-size:12px;}

    </style>

</head>
<body>

<htmlpageheader name="myheader">
    <br><br>
    <table width="100%"><tr>
            <td width="50%" style="color:#000;"><span style="font-weight: bold; font-size: 14pt;">
                    Order Pick Aid 008898934</span>
                <br />
                    (C00006) Ancient Wisdom Marketing Ltd
                <br/>
            </td>

            <td width="50%" style="text-align: right;">
                <div style="text-align: right">Order date: 20 Jun 23 15:24 UTC</div>
                <div style="text-align: right">Delivery note date: 20 Jun 23 15:24 UTC</div>
            </td>
        </tr>
    </table>
</htmlpageheader>

<sethtmlpageheader name="myheader" value="on" show-this-page="1" />
<sethtmlpagefooter name="myfooter" value="on" />
<br>
<table width="100%" style="font-family: sans-serif; margin-top: 40px;" cellpadding="10">
    <tr>
        <td width="45%" style="border: 0.1mm solid #888888;">
            <div>
                <div  class=" {if !$customer->get('Customer Main Plain Mobile')}hide{/if}">
                    <span class="address_label">Mobile</span> <span class="address_value">
                        +44 114 272 9165
                    </span>
                </div>
                <div class=" {if !$customer->get('Customer Main Plain Telephone')}hide{/if}">
                    <span class="address_label">Phone</span> <span class="address_value">
                        +44 114 272 9165
                    </span>
                </div>
            </div>

            <div class="data_field small {if $customer->get('Customer Main Plain Email')==''}hide{/if}" style="margin-top:5px">
                <span class="address_label">Email</span>  <span class="address_value">purchasing@ancientwisdom.biz</span>
            </div>

            <div style="height: 5px;border:0px solid red;font-size: 5px">&nbsp;</div>

            <div style="margin-top: 10px;padding-top: 10px;">
                <span class="address_label">Delivery Address:</span><br />
            </div>
            <div class="address_value">Bryant Dawson</div>
            <div class="address_value">Ancient Wisdom Marketing Ltd</div>
            <div class="address_value">Affinity Park</div>
            <div class="address_value">Europa Drive</div>
            <div class="address_value">SHEFFIELD</div>
            <div class="address_value">S9 1XT</div>
        </td>
        <td width="10%">&nbsp;</td>
        <td width="45%" style="border: 0.1mm solid #888888;font-size:9pt">
            <table id="order_pick_aid_data" cellspacing="0" cellpadding="0">
                <tr>
                    <td class="label">Picker:</td>
                    <td class="to_fill"></td>
                </tr>
                <tr>
                    <td class="label">Packer:</td>
                    <td class="to_fill"></td>
                </tr>
                <tr>
                    <td class="label">Weight:</td>
                    <td class="to_fill">℮375Kg</td>
                </tr>
                <tr>
                    <td class="label">Parcels:</td>
                    <td class="to_fill"></td>
                </tr>
                <tr>
                    <td class="label">Courier:</td>
                    <td class="to_fill"></td>
                </tr>
                <tr>
                    <td class="label">Consignment:</td>
                    <td class="to_fill"></td>
                </tr>
            </table>
    </tr>
</table>
<br>

<div style="float:left;height:150px;border:0.2mm  solid #000;margin-bottom:20px;padding:10px;width: 143.5mm;">
    <span style="font-size: 7pt; color: #555555; font-family: sans-serif;">Notes:</span>
    <div style="padding-top: 1mm">
        AWA01591
    </div>
</div>
<barcode style="float:left;margin-left: 20px;border:0px solid #ccc" code="AAAAA" type="QR" />

<div style=" clear:both;font-size: 9pt;margin-bottom:2pt"><b>4</b> items, 180 SKOs</div>

<table class="items" width="100%" style="font-size: 7pt; border-collapse: collapse;" cellpadding="8">
    <thead>
    <tr>
        <td align="left" width="14%">Location</td>
        <td align="center" width="14%">Reference</td>
        <td align="left" width="14%">Alt Locations</td>
        <td align="left">SKO description</td>
        <td align="center" width="7%">SKOs</td>
        <td align="left" width="16%">Notes</td>
    </tr>
    </thead>
    <tbody>

    <tr class="{if $smarty.foreach.products.last}last{/if} {if $smarty.foreach.products.iteration is even} even{/if} ">
        <td style="padding: 0px">
            <table style="width:100%; border-spacing:0; border-collapse:collapse;">
                <tr>

                    <td style="padding-left:10px;border:none;padding-top:8px"><b>11V2</b></td>
                    <td style="border:none;font-style: italic;padding-top:8px;text-align:right;padding-right: 10px"><span>39</span></td>
                </tr>
            </table>
        </td>
        <td align="center">ABPC-07</td>

        <td align="left" style="padding: 0px">
            <table style="width:100%; border-spacing:0; border-collapse:collapse;">

                <tr>

                    <td style="padding-left:10px;border:none;{if $smarty.foreach.locations.first}padding-top:8px;{else}border-top:.1mm solid #b0b0b0{/if}">11W6</td>
                    <td style="border:none;font-style: italic;{if $smarty.foreach.locations.first}padding-top:8px;{else}border-top:.1mm solid #b0b0b0;{/if}text-align:right;padding-right: 10px">60</td>
                </tr>

            </table>
        </td>
        <td align="left">Box of 5 Aromatherapy Bath Potion in Kraft Bags 350g - Stress Buster</td>
        <td align="center" >50</td>
        <td align="left" style="font-size: 6pt;"></td>
    </tr>

    </tbody>
</table>
<br>
<htmlpagefooter name="myfooter">
    <small style="font-size: 7pt;">Created: 2023-06-22 08:20:11 UTC</small>
    <div style="border-top: 1px solid #000000; font-size: 9pt; text-align: center; padding-top: 3mm; ">
        Page 1 of 1
    </div>
    <br><br>
</htmlpagefooter>
</body>
</html>

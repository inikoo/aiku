<html>
<head>
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

            <td style="text-align: right;">Order Number<br/>
                <b>00988412</b>
            </td>

        </tr>
    </table>
</htmlpageheader>

<sethtmlpageheader name="myheader" value="on" show-this-page="1"/>
<sethtmlpagefooter name="myfooter" value="on"/>

<table width="100%" style="margin-top: 40px">
    <tr>
        <td>
            <h1>
                Invoice ARi223123
            </h1>
        </td>
        <td style="text-align: right">
            <div>
                Invoice Date:<b>20 Jun 2023</b>
            </div>

            <div style="text-align: right">
                Tax liability date" <b>20 Jun 2023</b>
            </div>

            <div style="text-align: right">
                Order Date: <b>20 Jun 2023</b>
            </div>

            <div style="text-align: right">
                Invoice Number<b>Invoice ARi223123</b>
            </div>

            <div style="text-align: right">
                Customer's PO Reference:<b>AR04063</b>
            </div>
        </td>
    </tr>
</table>
<table width="100%" style="font-family: sans-serif; margin-top: 20px" cellpadding="0">
    <tr>
        <td width="50%" style="vertical-align:bottom;border: 0mm solid #888888;">
            <div>
                <div>
                    Payment State: <b>Paid in full</b>
                </div>
                <div>
                    Customer: <b>Emma Pratt</b>
                    (6391)
                </div>
                <div class=" {if !$customer->get('Customer Main Plain Mobile')}hide{/if}">
                    <span class="address_label">Mobile:</span> <span class="address_value">+44 7710 689446</span>
                </div>

                <div class=" {if !$customer->get('Customer Main Plain Telephone')}hide{/if}">
                    <span class="address_label">Phone:</span> <span class="address_value">+44 7710 689446</span>
                </div>
            </div>
        </td>
        <td width="50%" style="vertical-align:bottom;border: 0mm solid #888888;text-align: right">

            <div style="text-align:right;">
                <b>1 box</b>
            </div>
            <div style="text-align: right">Weight: <b>2Kg</b></div>

            <div style="text-align: right">
                Courier: <b> <span
                        id="formatted_consignment">XIWSKU</span></b>
            </div>

        </td>
    </tr>
</table>
<table width="100%" style="font-family: sans-serif;" cellpadding="10">
    <tr>
        <td width="45%" style="border: 0.1mm solid #888888;"><span
                style="font-size: 7pt; color: #555555; font-family: sans-serif;">Billing address:</span>
            <div>
                The Firs Stone Lodge Lane
            </div>
        </td>
        <td width="10%">&nbsp;</td>
        <td width="45%" style="border: 0.1mm solid #888888;">
            <span style="font-size: 7pt; color: #555555; font-family: sans-serif;">Delivery address:</span>
            <div>
                The Firs Stone Lodge Lane
            </div>
        </td>
    </tr>
</table>
<br>

<table class="items" width="100%" style="font-size: 9pt; border-collapse: collapse;" cellpadding="8">
    <thead>
    <tr>
        <td style="width:14%;text-align:left">Code</td>
        <td style="width:14%;text-align:left">Origin</td>

        <td style="text-align:left">Description</td>
        <td style="text-align:left;width:20% ">Discount</td>

        <td style="text-align:left">Qty</td>

        <td style="width:10%;text-align:right">Amount</td>
    </tr>
    </thead>
    <tbody>

    <tr class="{if $smarty.foreach.products.last}last{/if}">
        <td style="text-align:left">EOKG-14</td>
        <td style="text-align:left">Netherlands</td>

        <td style="text-align:left">
            1x Bergamot (FCF) Bulk Essential Oil (£50.00)
            Weight: 1Kg
            Origin: Côte d’Ivoire (CIV)
            Tariff Code: 3301294100
        </td>
        <td style="text-align:left">0</td>

        <td style="text-align:right">100</td>

        <td style="text-align:right">$50</td>
    </tr>

    </tbody>
    <tbody class="totals">
    <tr>
        <td style="border:none" colspan="4"></td>
        <td>Items Net</td>
        <td>$50</td>
    </tr>

    <tr>
        <td style="border:none" colspan="4"></td>
        <td>Refunds</td>
        <td>$40</td>
    </tr>

    <tr>
        <td style="border:none" colspan="4"></td>
        <td>Shipping</td>
        <td>$4</td>
    </tr>

    <tr>
        <td style="border:none" colspan="4"></td>
        <td>Charges</td>
        <td>$60</td>
    </tr>

    <tr>
        <td style="border:none" colspan="4"></td>
        <td>Insurance</td>
        <td>$12</td>
    </tr>

    <tr>
        <td style="border:none" colspan="4"></td>
        <td>Adjusts</td>
        <td>$120</td>
    </tr>

    <tr class="total_net">
        <td style="border:none" colspan="4"></td>
        <td>Total Net</td>
        <td>$230</td>
    </tr>

    <tr>
        <td style="border:none" colspan="4"></td>
        <td class="totals">TAX <br> <small>GB-SR VAT 20%</small></td>
        <td class="totals">$15</td>
    </tr>

    <tr class="total">
        <td style="border:none" colspan="4"></td>
        <td><b>Total</b></td>
        <td>$200</td>
    </tr>
    </tbody>

</table>

<br>

<table class="items" width="100%" style="font-size: 9pt; border-collapse: collapse;" cellpadding="8">
    <tr class="title">
        <td colspan="5">Payments</td>
    </tr>

    <tr class="title">
        <td style="width:40%;text-align:left">Method</td>
        <td style="text-align:right">Date</td>
        <td style="text-align:left">Status</td>
        <td style="text-align:left">Reference</td>
        <td style=";text-align:right">Amount</td>
    </tr>

    <tbody>
    <tr class="{if $smarty.foreach.payments.last}last{/if}">
        <td style="text-align:left">
            Credit Card
        </td>
        <td style="text-align:right">Fri 16 Jun
            2023 11:49
            UTC
        </td>
        <td style="text-align:left">Completed</td>
        <td style="text-align:left">pay_dcxzgw4y4u42bddqotwouaag3y</td>
        <td style="text-align:right">$200</td>
    </tr>
    </tbody>
</table>
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
    <div style="border-top: 1px solid #000000; font-size: 9pt; text-align: center; padding-top: 3mm; margin-top: 120px"></div>
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
</htmlpagefooter>

</body>
</html>

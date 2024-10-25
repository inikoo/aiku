<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Delivery Note - {{ $deliveryNote['reference'] }}</title>
    <style>
        body { font-family: sans-serif; font-size: 10pt; }
        h1 { font-size: 14pt; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        td, th { padding: 8px; border: 0.1mm solid #000; }
        .header, .footer { font-size: 9pt; text-align: center; }
        .address { font-size: 9pt; color: #555; }
        .items td { border: 0.1mm solid #000; }
        .items thead td { background-color: #EEEEEE; text-align: center; }
        .totals td { text-align: right; border: 0.1mm solid #000; }
        .total-row td { font-weight: bold; text-align: right; border-top: 0.3mm solid #000; }
    </style>
</head>
<body>

<!-- Header Section -->
<div class="header">
    <h1>Delivery Note</h1>
    <p>Delivery Note No: {{ $deliveryNote['reference'] }} | Date: {{ \Carbon\Carbon::parse($deliveryNote['date'])->format('Y-m-d') }}</p>
</div>

<!-- Company and Customer Details -->
<table>
    <tr>
        <td style="width: 50%;">
            <strong>From:</strong> <br>
            AW Aromatics Ltd <br>
            Unit 15, Parkwood Business Park <br>
            Sheffield S3 8AL, United Kingdom <br>
            Phone: 00441144384914 <br>
            Email: sales@aw-aromatics.com
        </td>
        <td style="width: 50%;">
            <strong>To:</strong> <br>
            {{ $customer['name'] ?? 'Customer Name' }}<br>
            {{ $deliveryAddress ?? 'Delivery Address' }}<br>
            United Kingdom
        </td>
    </tr>
</table>

<!-- Delivery Details -->
<table>
    <tr>
        <td><strong>Order Number:</strong> {{ $order['reference'] }}</td>
        <td><strong>Issued Date:</strong> {{ \Carbon\Carbon::parse($order['in_warehouse_at'])->format('Y-m-d') }}</td>
    </tr>
</table>

<!-- Items Section -->
<table class="items">
    <thead>
        <tr>
            <td>Item Code</td>
            <td>Item Name</td>
            <td>Quantity Required</td>
            <td>Quantity Picked</td>
            <td>Quantity Packed</td>
        </tr>
    </thead>
    <tbody>
        @foreach ($items as $item)
        <tr>
            <td>{{ $item->orgStock['code'] }}</td>
            <td>{{ $item->orgStock['name'] }}</td>
            <td>{{ $item->orgStock['quantity_required'] }}</td>
            <td></td>
            <td></td>
        </tr>
        @endforeach
    </tbody>
</table>

<!-- Footer Section -->
<div class="footer">
    <p>Thank you for your business!</p>
    <p>If you have any questions, please contact us at 00441144384914 or sales@aw-aromatics.com.</p>
</div>

</body>
</html>

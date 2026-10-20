<?php

/*
 * Author Louis Perez
 * Created on 15-10-2026-10h-06m
 * GitHub: https://github.com/louis-perez
 * Copyright 2026
*/

use App\Exports\Dispatching\DhlItemUploadCsvExport;
use App\Exports\Dispatching\DhlItemUploadValidator;

function dhlLine(array $overrides = []): array
{
    return array_merge([
        'product_code'        => 'JBB-01',
        'description'         => 'Jelly bath balls',
        'tariff_code'         => '33073000',
        'quantity'            => 2.0,
        'pack_units'          => 1.0,
        'unit_value'          => 12.5,
        'currency_code'       => 'EUR',
        'unit_weight_kg'      => 0.48,
        'origin_country_code' => 'CN',
        'tax_paid'            => false,
    ], $overrides);
}

it('writes the 14 DHL fields in order, per pack', function () {
    $export = new DhlItemUploadCsvExport([dhlLine()]);

    expect($export->rows())->toBe([
        ['1', 'INV_ITEM', 'Jelly bath balls', '33073000', '2', 'EA', '12.5', 'EUR', '0.48', '', 'CN', 'ABW', 'JBB-01', 'N'],
    ])
        ->and($export->validate())->toBe(['rows' => [], 'file' => []]);
});

it('counts a multi unit product in packs and says how many it holds', function () {
    $row = (new DhlItemUploadCsvExport([dhlLine(['pack_units' => 16.0, 'description' => 'Bath balls, lavender'])]))->rows()[0];

    expect($row[2])->toBe('Bath balls, lavender - pack of 16')
        ->and($row[4])->toBe('2')
        ->and($row[5])->toBe('SET');
});

it('keeps the value to 5 decimals and weight to 3', function () {
    $row = (new DhlItemUploadCsvExport([dhlLine(['unit_value' => 10 / 3, 'unit_weight_kg' => 0.0004 + 0.25])]))->rows()[0];

    expect($row[6])->toBe('3.33333')
        ->and($row[8])->toBe('0.25');
});

it('sends the DHL code where DHL does not use ISO', function () {
    $export = new DhlItemUploadCsvExport([dhlLine(['origin_country_code' => 'XK']), dhlLine(['origin_country_code' => 'CW'])]);

    expect(array_column($export->rows(), 10))->toBe(['KV', 'XC']);
});

it('marks tax paid only when tax was charged', function () {
    $export = new DhlItemUploadCsvExport([dhlLine(['tax_paid' => true]), dhlLine()]);

    expect(array_column($export->rows(), 13))->toBe(['Y', 'N']);
});

it('reports each failing row and column against the DHL rules', function () {
    $export = new DhlItemUploadCsvExport([
        dhlLine(),
        dhlLine(['tariff_code' => null]),
        dhlLine(['tariff_code' => '3307 30', 'description' => 'Bombe de bain à la lavande']),
        dhlLine(['origin_country_code' => null, 'unit_weight_kg' => null]),
        dhlLine(['quantity' => 1.5]),
    ]);

    $rows = $export->validate()['rows'];

    expect(array_keys($rows))->toBe([1, 2, 3, 4])
        ->and(array_keys($rows[1]))->toBe([3])
        ->and(array_keys($rows[2]))->toBe([2, 3])
        ->and(array_keys($rows[3]))->toBe([8, 10])
        ->and(array_keys($rows[4]))->toBe([4])
        ->and($rows[4][4][0])->toBe('Quantity must be a whole number above 0.');
});

it('checks the file as a whole: not empty, at most 250 items, one currency', function () {
    expect((new DhlItemUploadCsvExport([]))->validate()['file'])->toHaveCount(1)
        ->and((new DhlItemUploadCsvExport(array_fill(0, 251, dhlLine())))->validate()['file'])->toHaveCount(1)
        ->and((new DhlItemUploadCsvExport([dhlLine(), dhlLine(['currency_code' => 'GBP'])]))->validate()['file'][0])->toContain('EUR, GBP');
});

it('rejects DHL rule breaks in any field', function () {
    $validator = new DhlItemUploadValidator();
    $row       = (new DhlItemUploadCsvExport([dhlLine()]))->rows()[0];

    $broken = [
        2  => str_repeat('a', 171),
        5  => 'PCE',
        6  => '.5',
        7  => 'EURO',
        8  => '1.2345',
        9  => '1',
        11 => 'XYZ',
        13 => 'YES',
    ];

    foreach ($broken as $column => $value) {
        $bad          = $row;
        $bad[$column] = $value;

        expect(array_keys($validator->validate([$bad])['rows'][0] ?? []))->toBe([$column]);
    }
});

it('takes the delimiter out of the description and flags it anywhere else', function () {
    $export = new DhlItemUploadCsvExport([dhlLine(['description' => 'Bath balls, lavender', 'product_code' => 'JBB,01'])], ',');

    expect($export->rows()[0][2])->toBe('Bath balls lavender')
        ->and(array_keys($export->validate()['rows'][0]))->toBe([12])
        ->and((new DhlItemUploadCsvExport([dhlLine(['product_code' => 'JBB|01'])]))->validate()['rows'][0][12][0])->toContain('|');
});

it('writes rows without quotes unless a text delimiter is given', function () {
    $csv = fn (DhlItemUploadCsvExport $export) => (function () use ($export) {
        ob_start();
        $export->download('items.csv')->sendContent();

        return ob_get_clean();
    })();

    expect($csv(new DhlItemUploadCsvExport([dhlLine()])))->toBe("1|INV_ITEM|Jelly bath balls|33073000|2|EA|12.5|EUR|0.48||CN|ABW|JBB-01|N\n")
        ->and($csv(new DhlItemUploadCsvExport([dhlLine()], ';', '"')))->toBe("1;INV_ITEM;\"Jelly bath balls\";33073000;2;EA;12.5;EUR;0.48;;CN;ABW;JBB-01;N\n");
});

it('uses the values typed in the preview for the file, without touching the generated ones', function () {
    $export = new DhlItemUploadCsvExport([dhlLine(['tariff_code' => null, 'origin_country_code' => null]), dhlLine()], edits: [
        0 => [3 => ' 33079000 ', 10 => 'GB'],
        1 => [99 => 'ignored'],
        5 => [3 => 'ignored'],
    ]);

    expect($export->rows()[0][3])->toBe('33079000')
        ->and($export->rows()[0][10])->toBe('GB')
        ->and($export->generatedRows()[0][3])->toBe('')
        ->and($export->rows()[1])->toBe($export->generatedRows()[1])
        ->and($export->rows())->toHaveCount(2)
        ->and($export->validate())->toBe(['rows' => [], 'file' => []]);
});

it('checks the typed values against DHL\'s rules', function () {
    $errors = (new DhlItemUploadCsvExport([dhlLine()], edits: [0 => [10 => 'gbr', 3 => '']]))->validate();

    expect(array_keys($errors['rows'][0]))->toEqualCanonicalizing([3, 10]);
});

it('drops floating point noise from the quantity', function () {
    expect((new DhlItemUploadCsvExport([dhlLine(['quantity' => 90.00000000000001])]))->rows()[0][4])->toBe('90');
});

it('leaves out of stock and zero value lines out of the customs file, as the invoice does', function () {
    $items = \App\Actions\Dispatching\DeliveryNote\CustomsExport\GetDeliveryNoteCustomsItems::make();

    expect($items->isDeclared(dhlLine()))->toBeTrue()
        ->and($items->isDeclared(dhlLine(['quantity' => 0.0, 'unit_value' => null])))->toBeFalse()
        ->and($items->isDeclared(dhlLine(['unit_value' => 0.0])))->toBeFalse();
});

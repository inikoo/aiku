<?php

/*
 * Author Louis Perez
 * Created on 15-10-2026-10h-06m
 * GitHub: https://github.com/louis-perez
 * Copyright 2026
*/

namespace App\Exports\Dispatching;

use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DhlItemUploadCsvExport
{
    public const array COUNTRY_CODES = [
        'XK' => 'KV',
        'CW' => 'XC',
        'SX' => 'XM',
        'BL' => 'XY',
        'PF' => 'TI',
    ];

    /**
     * @param array<int, array{
     *     product_code: string|null,
     *     description: string|null,
     *     tariff_code: string|null,
     *     quantity: float,
     *     pack_units: float,
     *     unit_value: float|null,
     *     currency_code: string|null,
     *     unit_weight_kg: float|null,
     *     origin_country_code: string|null,
     *     tax_paid: bool
     * }> $lines
     */
    public function __construct(public array $lines)
    {
    }

    /**
     * @return array<int, string>
     */
    public function columns(): array
    {
        return [
            'Unique item number',
            'Item',
            'Item description',
            'Commodity code',
            'Quantity',
            'Units',
            'Value',
            'Currency',
            'Weight',
            'Weight 2',
            'Country of origin',
            'Reference type',
            'Reference details',
            'Tax paid',
        ];
    }

    /**
     * @return array{rows: array<int, array<int, array<int, string>>>, file: array<int, string>}
     */
    public function validate(): array
    {
        return (new DhlItemUploadValidator())->validate($this->rows());
    }

    /**
     * @return array<int, array<int, string>>
     */
    public function rows(): array
    {
        return array_map(fn (array $line) => [
            '1',
            'INV_ITEM',
            $this->description($line),
            (string) $line['tariff_code'],
            trimDecimalZeros($line['quantity']),
            $line['pack_units'] > 1 ? 'SET' : 'EA',
            $line['unit_value'] === null ? '' : $this->decimal($line['unit_value'], 5),
            (string) $line['currency_code'],
            $line['unit_weight_kg'] === null ? '' : $this->decimal($line['unit_weight_kg'], 3),
            '',
            self::COUNTRY_CODES[$line['origin_country_code']] ?? (string) $line['origin_country_code'],
            $line['product_code'] ? 'ABW' : '',
            Str::limit((string) $line['product_code'], 35, ''),
            $line['tax_paid'] ? 'Y' : 'N',
        ], $this->lines);
    }

    public function download(string $filename): StreamedResponse
    {
        $rows = $this->rows();

        return response()->streamDownload(function () use ($rows) {
            $handle = fopen('php://output', 'w');
            foreach ($rows as $row) {
                fputcsv($handle, $row, ',', '"', '');
            }
            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    private function description(array $line): string
    {
        $description = trim(preg_replace('/\s+/', ' ', str_replace(',', ' ', (string) $line['description'])));
        $packSuffix  = $line['pack_units'] > 1 ? ' - pack of '.trimDecimalZeros($line['pack_units']) : '';

        return Str::limit($description, 170 - strlen($packSuffix), '').$packSuffix;
    }

    private function decimal(float $value, int $decimals): string
    {
        return rtrim(rtrim(number_format($value, $decimals, '.', ''), '0'), '.') ?: '0';
    }
}

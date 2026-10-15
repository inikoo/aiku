<?php

/*
 * Author Louis Perez
 * Created on 15-10-2026-11h-40m
 * GitHub: https://github.com/louis-perez
 * Copyright 2026
*/

namespace App\Exports\Dispatching;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class DhlItemUploadValidator
{
    public const int MAX_ITEMS = 250;

    public const array UNIT_CODES = [
        'BOX', '2GM', '2M', '2M4', '3M4', 'M3', 'DPR', 'DOZ', 'PCS', 'GM', 'GRS', 'KG', 'L', 'M', 'X', 'NO', '2KG', 'PRS', '2L',
        '3KG', 'CM2', '2M2', '3M2', 'CT', 'FT', 'IN', 'SK', 'SET', 'MM', 'YD', 'MG', 'SYD', 'ML', 'EA', 'M2',
    ];

    public const array REFERENCE_TYPES = ['SE', 'MID', 'SON', 'BRD', 'MAK', 'AFE', 'AAJ', 'PAN', 'ABW'];

    public const array FIELDS = [
        'id',
        'item',
        'description',
        'commodity_code',
        'quantity',
        'units',
        'value',
        'currency',
        'weight',
        'weight_2',
        'country_of_origin',
        'reference_type',
        'reference_details',
        'tax_paid',
    ];

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'id'                => ['required', Rule::in(['1'])],
            'item'              => ['required', Rule::in(['INV_ITEM'])],
            'description'       => ['required', 'max:170', 'regex:/^[\x20-\x7E]+$/'],
            'commodity_code'    => ['required', 'regex:/^[0-9.]{6,15}$/'],
            'quantity'          => ['required', 'regex:/^[1-9][0-9]*$/'],
            'units'             => ['required', Rule::in(self::UNIT_CODES)],
            'value'             => ['required', 'max:18', 'regex:/^\d+([.,]\d{1,5})?$/'],
            'currency'          => ['required', 'size:3', 'regex:/^[A-Z]{3}$/'],
            'weight'            => ['required', 'regex:/^\d+([.,]\d{1,3})?$/'],
            'weight_2'          => ['prohibited'],
            'country_of_origin' => ['required', 'regex:/^[A-Z]{2}$/'],
            'reference_type'    => ['nullable', Rule::in(self::REFERENCE_TYPES)],
            'reference_details' => ['nullable', 'required_with:reference_type', 'max:35'],
            'tax_paid'          => ['nullable', Rule::in(['Y', 'N'])],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'description.regex'       => __('Description must be in English, without accents or special characters.'),
            'commodity_code.regex'    => __('Commodity code must be 6 to 15 digits or periods.'),
            'quantity.regex'          => __('Quantity must be a whole number above 0.'),
            'units.in'                => __('Units must be one of DHL\'s unit codes.'),
            'value.regex'             => __('Value must be a number with at most 5 decimals and a 0 before the decimal point.'),
            'currency.regex'          => __('Currency must be a 3 letter code.'),
            'weight.regex'            => __('Weight must be a number with at most 3 decimals and a 0 before the decimal point.'),
            'weight_2.prohibited'     => __('Weight 2 must be left blank.'),
            'country_of_origin.regex' => __('Country of origin must be a 2 letter code.'),
            'reference_type.in'       => __('Reference type must be one of DHL\'s reference codes.'),
        ];
    }

    /**
     * @param array<int, array<int, string>> $rows
     *
     * @return array{rows: array<int, array<int, array<int, string>>>, file: array<int, string>}
     */
    public function validate(array $rows): array
    {
        $rowErrors = [];

        foreach ($rows as $rowIndex => $row) {
            $data      = array_combine(self::FIELDS, array_map(fn ($cell) => $cell === '' ? null : $cell, $row));
            $validator = Validator::make($data, $this->rules(), $this->messages());

            foreach ($validator->errors()->messages() as $field => $messages) {
                $rowErrors[$rowIndex][array_search($field, self::FIELDS)] = $messages;
            }
        }

        return [
            'rows' => $rowErrors,
            'file' => $this->fileErrors($rows),
        ];
    }

    /**
     * @param array<int, array<int, string>> $rows
     *
     * @return array<int, string>
     */
    private function fileErrors(array $rows): array
    {
        $errors = [];

        if (count($rows) === 0) {
            $errors[] = __('There are no products to export.');
        }

        if (count($rows) > self::MAX_ITEMS) {
            $errors[] = __('DHL takes at most :max items per file, this delivery has :count.', ['max' => self::MAX_ITEMS, 'count' => count($rows)]);
        }

        $currencies = array_values(array_unique(array_filter(array_column($rows, 7))));
        if (count($currencies) > 1) {
            $errors[] = __('All items must be in one currency, found :currencies.', ['currencies' => implode(', ', $currencies)]);
        }

        return $errors;
    }
}

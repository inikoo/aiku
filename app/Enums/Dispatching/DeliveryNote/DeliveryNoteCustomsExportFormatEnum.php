<?php

/*
 * Author Louis Perez
 * Created on 15-10-2026-10h-06m
 * GitHub: https://github.com/louis-perez
 * Copyright 2026
*/

namespace App\Enums\Dispatching\DeliveryNote;

use App\Enums\EnumHelperTrait;
use App\Exports\Dispatching\DhlItemUploadCsvExport;

enum DeliveryNoteCustomsExportFormatEnum: string
{
    use EnumHelperTrait;

    case DHL_CSV = 'dhl_csv';

    public function label(): string
    {
        return match ($this) {
            self::DHL_CSV => __('DHL item upload (CSV)'),
        };
    }

    public function exporter(array $lines, string $delimiter, string $textDelimiter, array $edits = []): DhlItemUploadCsvExport
    {
        return match ($this) {
            self::DHL_CSV => new DhlItemUploadCsvExport($lines, $delimiter, $textDelimiter, $edits),
        };
    }

    /**
     * @return array<int, array{value: string, label: string}>
     */
    public static function options(): array
    {
        return array_map(fn (self $format) => ['value' => $format->value, 'label' => $format->label()], self::cases());
    }
}

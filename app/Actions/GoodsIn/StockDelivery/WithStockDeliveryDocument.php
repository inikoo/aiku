<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 13 Oct 2026, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\GoodsIn\StockDelivery;

use App\Models\Helpers\Media;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Csv;
use Smalot\PdfParser\Parser as PdfParser;
use Throwable;

/**
 * Turns a document attached to a stock delivery into what a model reads and into the plain text its reading is
 * checked against. A scan or a photo has no text, so its reading can only be checked by a person.
 */
trait WithStockDeliveryDocument
{
    /**
     * @return array<string, mixed>
     */
    protected function documentPart(Media $media, string $content): array
    {
        $mimeType = (string) $media->mime_type;

        if ($mimeType === 'application/pdf') {
            return ['type' => 'file', 'file' => ['filename' => $media->file_name, 'file_data' => 'data:application/pdf;base64,'.base64_encode($content)]];
        }

        if (Str::startsWith($mimeType, 'image/')) {
            return ['type' => 'image_url', 'image_url' => ['url' => "data:$mimeType;base64,".base64_encode($content)]];
        }

        return ['type' => 'text', 'text' => "The invoice, as CSV:\n".$this->spreadsheetAsCsv($content, $media->file_name)];
    }

    protected function documentText(Media $media, string $content): string
    {
        $mimeType = (string) $media->mime_type;

        if (Str::startsWith($mimeType, 'image/')) {
            return '';
        }

        try {
            return $mimeType === 'application/pdf'
                ? (new PdfParser())->parseContent($content)->getText()
                : $this->spreadsheetAsCsv($content, $media->file_name);
        } catch (Throwable) {
            return '';
        }
    }

    protected function spreadsheetAsCsv(string $content, string $fileName): string
    {
        $base = tempnam(sys_get_temp_dir(), 'invoice-');
        $path = $base.'.'.pathinfo($fileName, PATHINFO_EXTENSION);

        try {
            file_put_contents($path, $content);
            $spreadsheet = IOFactory::load($path);
            $csv         = '';

            foreach ($spreadsheet->getAllSheets() as $index => $sheet) {
                ob_start();
                (new Csv($spreadsheet))->setSheetIndex($index)->save('php://output');
                $csv .= '# '.$sheet->getTitle()."\n".ob_get_clean()."\n";
            }

            return $csv;
        } finally {
            @unlink($path);
            @unlink($base);
        }
    }
}

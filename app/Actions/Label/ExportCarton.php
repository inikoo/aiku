<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Fri, 23 Jun 2023 11:43:59 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Label;

use App\Actions\Traits\WithExportData;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;
use Mccarlosen\LaravelMpdf\Facades\LaravelMpdf as PDF;
use Symfony\Component\HttpFoundation\Response;

class ExportCarton
{
    use AsAction;
    use WithAttributes;
    use WithExportData;

    /**
     * @throws \Mpdf\MpdfException
     */
    public function handle(ActionRequest $request): Response
    {
        $width  = $request->get('width');
        $height = $request->get('height');

        $config = [
            'format' => [$width, $height],
            'margin_left' => 2,
            'margin_right' => 2,
            'margin_top' => 2,
            'margin_bottom' => 2,
            'auto_page_break' => true,
            'auto_page_break_margin' => 10
        ];

        $headerFontSize  = 2.5;
        $contentFontSize = 2.0;
        $labelFontSize   = 1.5;
        $barcodeSize     = 0.5;

        if($width == 63) {
            $config['default_font_size'] = '2';
            $headerFontSize  = 1.0;
            $contentFontSize = 0.8;
            $labelFontSize   = 0.5;
            $barcodeSize     = 0.3;
        }

        if($width == 63.5) {
            $config['default_font_size'] = '2';
            $headerFontSize  = 1.0;
            $contentFontSize = 0.8;
            $labelFontSize   = 0.5;
            $barcodeSize     = 0.3;
        }

        if($width == 70) {
            $config['default_font_size'] = '2';
            $headerFontSize  = 1.0;
            $contentFontSize = 0.8;
            $labelFontSize   = 0.5;
            $barcodeSize     = 0.3;
        }

        if($width == 125) {
            $config['default_font_size'] = '3';
            $headerFontSize  = 1.5;
            $contentFontSize = 1.0;
            $labelFontSize   = 1.0;
        }

        if($width == 130) {
            $config['default_font_size'] = '4';
            $headerFontSize  = 2.5;
            $contentFontSize = 2.0;
            $labelFontSize   = 1.5;
            $barcodeSize     = 1.0;
        }

        if($width == 140) {
            $config['default_font_size'] = '20';
        }

        $filename = 'carton-' . now()->format('Y-m-d');
        $pdf = PDF::loadView('labels.templates.pdf.carton', [
            'filename' => $filename,
            'headerFontSize' => $headerFontSize,
            'contentFontSize' => $contentFontSize,
            'labelFontSize' => $labelFontSize,
            'barcodeSize' => $barcodeSize
        ], [], $config);

        return $pdf->stream($filename . '.pdf');
    }

    /**
     * @throws \Mpdf\MpdfException
     */
    public function asController(ActionRequest $request): Response
    {
        return $this->handle($request);
    }
}

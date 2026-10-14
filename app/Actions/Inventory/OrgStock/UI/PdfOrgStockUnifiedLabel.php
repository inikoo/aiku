<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 13 Oct 2026
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Inventory\OrgStock\UI;

use App\Models\Inventory\OrgStock;
use App\Models\SupplyChain\SupplierProduct;
use Lorisleiva\Actions\Concerns\AsObject;
use Mccarlosen\LaravelMpdf\Facades\LaravelMpdf as PDF;
use Picqer\Barcode\Renderers\SvgRenderer;
use Picqer\Barcode\Types\TypeCode128;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

/**
 * Prints the unified unit, SKO or carton label on its own 100 mm wide page, or refuses with every
 * reason it cannot: a unified label is never printed with a hole in it.
 */
class PdfOrgStockUnifiedLabel
{
    use AsObject;

    private const MARGIN_MM = 2.5;

    private const QUIET_ZONE_MODULES = 10;

    /**
     * Hand-held scanners read a Code 128 bar from 0.25 mm; under that the batch is too long for the
     * width of the roll and the label is refused rather than printed unreadable.
     */
    private const MODULE_MM = ['min' => 0.25, 'max' => 0.33];

    /**
     * @throws \Mpdf\MpdfException
     */
    public function handle(OrgStock $orgStock, string $level, ?SupplierProduct $supplierProduct = null, ?string $batch = null): Response
    {
        $label    = GetOrgStockUnifiedLabelData::run($orgStock, $level, $supplierProduct, $batch);
        $blockers = $label['blockers'];
        $barcode  = $label['gs1_128_data'] ? $this->getGs1Barcode($label['gs1_128_data'], $label['size']['width']) : null;

        if ($label['gs1_128_data'] && !$barcode && !$blockers) {
            $blockers[] = __('The batch code is too long for a barcode that scans on this label');
        }

        abort_if((bool) $blockers, 422, implode(' · ', $blockers));

        $filename = 'label-'.$orgStock->code.'-'.$label['level'].'-unified.pdf';

        $pdf = PDF::loadView('labels.templates.pdf.org_stock.unified.'.$label['level'], [
            'label'   => $label,
            'barcode' => $barcode,
        ], [], [
            'title'         => $filename,
            'format'        => [$label['size']['width'], $label['size']['height']],
            'margin_left'   => self::MARGIN_MM,
            'margin_right'  => self::MARGIN_MM,
            'margin_top'    => 2,
            'margin_bottom' => 1.5,
            'margin_header' => 0,
            'margin_footer' => 0,
        ]);

        $output = $pdf->output();

        abort_if($pdf->getMpdf()->page > 1, 422, __('The texts of this product do not fit on the label: shorten the warnings'));

        return response($output, 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="'.$filename.'"');
    }

    /**
     * Drawn as vector bars, never a scaled picture, at the widest bar the roll leaves room for once
     * both quiet zones are kept clear.
     *
     * @return array{uri: string, width: string, height: string}|null
     */
    private function getGs1Barcode(string $data, float $labelWidth): ?array
    {
        try {
            $barcode = (new TypeCode128())->getBarcode($data);
        } catch (Throwable) {
            return null;
        }

        $module = min(self::MODULE_MM['max'], ($labelWidth - 2 * self::MARGIN_MM) / ($barcode->getWidth() + 2 * self::QUIET_ZONE_MODULES));

        if ($module < self::MODULE_MM['min']) {
            return null;
        }

        return [
            'uri'    => 'data:image/svg+xml;base64,'.base64_encode((new SvgRenderer())->render($barcode, $barcode->getWidth(), 40)),
            'width'  => round($barcode->getWidth() * $module, 2).'mm',
            'height' => '15mm',
        ];
    }
}

<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Tue, 26 Mar 2024 09:43:44 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\PalletReturn;

use App\Actions\Traits\WithExportData;
use App\Models\Fulfilment\PalletReturn;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;
use Mccarlosen\LaravelMpdf\Facades\LaravelMpdf as PDF;
use Symfony\Component\HttpFoundation\Response;

class PdfPalletReturn
{
    use AsAction;
    use WithAttributes;
    use WithExportData;

    /**
     * @throws \Mpdf\MpdfException
     */
    public function handle(PalletReturn $palletReturn): Response
    {
        $filename = 'pallet-return-' . $palletReturn->slug . '.pdf';

        $config = [
            'title'                  => $filename,
            'margin_left'            => 8,
            'margin_right'           => 8,
            'margin_top'             => 2,
            'margin_bottom'          => 2,
            'auto_page_break'        => true,
            'auto_page_break_margin' => 10
        ];

        $pdf = PDF::chunkLoadView('<html-separator/>', 'pickings.templates.pdf.return', [
            'filename'     => $filename,
            'return'       => $palletReturn,
            'customer'     => $palletReturn->fulfilmentCustomer->customer,
            'shop'         => $palletReturn->fulfilment->shop,
            'organisation' => $palletReturn->organisation,
        ], [], $config);

        return $pdf->stream($filename);
    }

    /**
     * @throws \Mpdf\MpdfException
     */
    public function asController(PalletReturn $palletReturn, ActionRequest $request): Response
    {
        return $this->handle($palletReturn);
    }
}

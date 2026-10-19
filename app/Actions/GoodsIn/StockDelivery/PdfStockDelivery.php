<?php

namespace App\Actions\GoodsIn\StockDelivery;

use App\Actions\OrgAction;
use App\Actions\Procurement\WithAgentOrganisation;
use App\Actions\Traits\Authorisations\WithProcurementAuthorisation;
use App\Models\GoodsIn\StockDelivery;
use App\Models\SysAdmin\Organisation;
use Carbon\Carbon;
use Lorisleiva\Actions\ActionRequest;
use Mccarlosen\LaravelMpdf\Facades\LaravelMpdf as PDF;
use Mpdf\MpdfException;
use Symfony\Component\HttpFoundation\Response;

class PdfStockDelivery extends OrgAction
{
    use WithProcurementAuthorisation;
    use WithAgentOrganisation;

    /**
     * @throws MpdfException
     */
    public function handle(StockDelivery $stockDelivery): Response
    {
        $stockDelivery->loadMissing(['items.orgStock', 'organisation', 'currency']);

        $pdf = PDF::loadView('goodsIn.templates.pdf.stock-delivery', [
            'stockDelivery' => $stockDelivery,
            'items'         => $stockDelivery->items,
        ]);

        return response($pdf->output(), 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="'.$this->filename($stockDelivery).'"');
    }

    public function filename(StockDelivery $stockDelivery): string
    {
        return preg_replace('/[^A-Za-z0-9._-]/', '-', $stockDelivery->reference).'-'.Carbon::now()->format('Y-m-d').'.pdf';
    }

    public function asController(Organisation $organisation, StockDelivery $stockDelivery, ActionRequest $request): Response
    {
        $this->initialisation($organisation, $request);
        $this->authorizeProcurementRecord($stockDelivery);

        return $this->handle($stockDelivery);
    }
}

<?php

/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Wed, 21 Oct 2026, Bali, Indonesia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\PalletReturn\Pdf;

use App\Enums\Fulfilment\PalletReturn\PalletReturnAttachmentScopeEnum;
use App\Models\Fulfilment\PalletReturn;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Symfony\Component\HttpFoundation\Response;

class PdfPalletReturnParcelDocuments
{
    use AsAction;
    use WithPalletReturnPrintDocuments;

    public function handle(PalletReturn $palletReturn): Response
    {
        return $this->mergedPdfResponse(
            [
                ...$this->attachmentDocuments($palletReturn, [PalletReturnAttachmentScopeEnum::INVOICE->value, PalletReturnAttachmentScopeEnum::DELIVERY_NOTE->value]),
                ...$this->productManualDocuments($palletReturn),
                ...$this->attachmentDocuments($palletReturn, [PalletReturnAttachmentScopeEnum::LEAFLET->value]),
            ],
            'documents-'.$palletReturn->slug.'.pdf',
            __('There is no invoice, delivery note or leaflet attached to this return, and no manual on its products.')
        );
    }

    public function asController(PalletReturn $palletReturn, ActionRequest $request): Response
    {
        return $this->handle($palletReturn);
    }
}

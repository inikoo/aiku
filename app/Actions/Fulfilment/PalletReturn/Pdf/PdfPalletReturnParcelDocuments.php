<?php

/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Wed, 21 Oct 2026, Bali, Indonesia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\PalletReturn\Pdf;

use App\Actions\Traits\Authorisations\WithPalletDocumentAuthorisation;
use App\Enums\Fulfilment\PalletReturn\PalletReturnAttachmentScopeEnum;
use App\Models\Fulfilment\PalletReturn;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Symfony\Component\HttpFoundation\Response;

class PdfPalletReturnParcelDocuments
{
    use WithPalletDocumentAuthorisation;
    use AsAction;
    use WithPalletReturnPrintDocuments;

    public function handle(PalletReturn $palletReturn): Response
    {
        return $this->mergedPdfResponse(
            $this->documents($palletReturn),
            'documents-'.$palletReturn->slug.'.pdf',
            __('There is no invoice or delivery note attached to this return, and no manual on its products.')
        );
    }

    /**
     * @return array<int, array{name: string, pdf: string}>
     */
    public function documents(PalletReturn $palletReturn): array
    {
        return [
            ...$this->attachmentDocuments($palletReturn, PalletReturnAttachmentScopeEnum::parcelDocumentScopes()),
            ...$this->productManualDocuments($palletReturn),
        ];
    }

    public function asController(PalletReturn $palletReturn, ActionRequest $request): Response
    {
        return $this->handle($palletReturn);
    }
}

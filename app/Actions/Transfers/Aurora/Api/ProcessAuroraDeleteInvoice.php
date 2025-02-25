<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 25 Feb 2025 00:16:07 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2025, Raul A Perusquia Flores
 */

namespace App\Actions\Transfers\Aurora\Api;

use App\Actions\Accounting\Invoice\DeleteInvoice;
use App\Actions\OrgAction;
use App\Models\Accounting\Invoice;
use App\Models\SysAdmin\Organisation;
use Lorisleiva\Actions\ActionRequest;

/**
 * @property string $fetcher
 */
class ProcessAuroraDeleteInvoice extends OrgAction
{
    use WithProcessAurora;

    public function rules(): array
    {
        return [
            'id'              => ['required', 'integer'],
            'bg'              => ['sometimes', 'boolean'],
            'delay'           => ['sometimes', 'integer']
        ];
    }


    /**
     * @throws \Exception
     */
    public function asController(Organisation $organisation, ActionRequest $request): array
    {
        $res = [
            'status'  => 'error',
            'message' => 'Invoice not found',
            'model'   => 'DeleteInvoice'
        ];

        $this->initialisation($organisation, $request);
        $validatedData = $this->validatedData;

        $invoice = Invoice::where('source_id', $organisation->id.':'.$validatedData['id'])->first();

        if ($invoice) {
            DeleteInvoice::make()->action($invoice, []);
            $res = [
                'status' => 'ok',
                'id'     => $invoice->source_id,
                'model'  => 'DeleteInvoice',
            ];
        }

        return $res;
    }

}

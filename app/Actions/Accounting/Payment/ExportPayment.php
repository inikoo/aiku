<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 24 Apr 2023 20:23:18 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Accounting\Payment;

use App\Actions\Traits\WithExportData;
use App\Enums\Helpers\Export\ExportTypeEnum;
use App\Exports\Accounting\PaymentAccountExport;
use App\Exports\Accounting\PaymentExport;
use App\Exports\Accounting\PaymentServiceProviderExport;
use App\Exports\HumanResources\WorkingPlacesExport;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use App\Exports\User\UsersExport;

class ExportPayment
{
    use AsAction;
    use WithAttributes;
    use WithExportData;

    /**
     * @throws \Throwable
     */
    public function handle(array $objectData): BinaryFileResponse
    {
        $type = $objectData['type'];

        return $this->export(new PaymentExport, 'payments', $type);
    }

    /**
     * @throws \Throwable
     */
    public function asController(ActionRequest $request): BinaryFileResponse
    {
        $this->setRawAttributes($request->all());
        $this->validateAttributes();

        return $this->handle($request->all());
    }
}

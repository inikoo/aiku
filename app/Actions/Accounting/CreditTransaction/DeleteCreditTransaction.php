<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 15 Jun 2024 00:11:33 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Accounting\CreditTransaction;

use AlibabaCloud\SDK\Dm\V20151123\Models\GetIpfilterListResponseBody\data;
use App\Actions\CRM\Customer\Hydrators\CustomerHydrateCreditTransactions;
use App\Actions\OrgAction;
use App\Actions\Traits\WithActionUpdate;
use App\Enums\Accounting\Invoice\CreditTransactionTypeEnum;
use App\Models\Accounting\CreditTransaction;
use App\Models\Accounting\Invoice;
use App\Models\Catalogue\Product;
use App\Models\Catalogue\Service;
use App\Models\CRM\Customer;
use App\Models\Fulfilment\PalletDelivery;
use App\Models\Fulfilment\PalletReturn;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\Concerns\AsAction;

class DeleteCreditTransaction extends OrgAction
{
    public function handle(CreditTransaction $creditTransaction): void
    {
        $customer = $creditTransaction->customer;
        $creditTransaction->delete();

        CustomerHydrateCreditTransactions::run($customer);
    }

    public function action(CreditTransaction $creditTransaction): void
    {
        $this->asAction = true;
        $this->initialisationFromShop($creditTransaction->shop, []);
        $this->handle($creditTransaction);
    }
}
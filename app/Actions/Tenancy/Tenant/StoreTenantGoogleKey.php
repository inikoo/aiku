<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 23 Apr 2023 11:33:30 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Tenancy\Tenant;

use App\Actions\Accounting\PaymentServiceProvider\StorePaymentServiceProvider;
use App\Actions\Assets\Currency\SetCurrencyHistoricFields;
use App\Actions\Mail\Mailroom\StoreMailroom;
use App\Actions\Tenancy\Group\Hydrators\GroupHydrateTenants;
use App\Actions\Tenancy\Group\StoreGroup;
use App\Enums\Mail\Mailroom\MailroomCodeEnum;
use App\Models\Assets\Country;
use App\Models\Assets\Currency;
use App\Models\Assets\Language;
use App\Models\Assets\Timezone;
use App\Models\Tenancy\Group;
use App\Models\Tenancy\Tenant;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class StoreTenantGoogleKey
{
    use AsAction;
    use WithAttributes;

    public function handle(Tenant $tenant, array $modelData): Tenant
    {
        $tenant->update([
            'data' => json_encode([
                'google_cloud_client_id' => $modelData['google_cloud_client_id'],
                'google_cloud_client_secret' => $modelData['google_cloud_client_secret']
            ])
        ]);

        return $tenant;
    }

    public function action(Tenant $tenant, $modelData): Tenant
    {
        return $this->handle($tenant, $modelData);
    }
}

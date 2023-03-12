<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 10 Mar 2023 20:59:01 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\SourceFetch\Aurora;

use App\Actions\Mailroom\Mailshot\StoreMailshot;
use App\Actions\Mailroom\Mailshot\UpdateMailshot;
use App\Models\Mailroom\Mailshot;
use App\Services\Tenant\SourceTenantService;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use JetBrains\PhpStorm\NoReturn;

class FetchMailshots extends FetchAction
{
    public string $commandSignature = 'fetch:mailshots {tenants?*} {--s|source_id=}';


    #[NoReturn] public function handle(SourceTenantService $tenantSource, int $tenantSourceId): ?Mailshot
    {
        if ($shopData = $tenantSource->fetchMailshot($tenantSourceId)) {
            if ($mailshot = Mailshot::where('source_id', $shopData['mailshot']['source_id'])
                ->first()) {
                $mailshot = UpdateMailshot::run(
                    mailshot: $mailshot,
                    modelData: $shopData['mailshot']
                );
            } else {
                $mailshot = StoreMailshot::run(
                    outbox: $shopData['outbox'],
                    modelData: $shopData['mailshot']
                );
            }

            DB::connection('aurora')->table('Email Campaign Dimension')
                ->where('Email Campaign Key', $mailshot->source_id)
                ->update(['aiku_id' => $mailshot->id]);
            return $mailshot;
        }

        return null;
    }


    public function getModelsQuery(): Builder
    {
        return DB::connection('aurora')
            ->table('Email Campaign Dimension')
            ->select('Email Campaign Key as source_id')
            ->orderBy('source_id');
    }

    public function count(): ?int
    {
        return DB::connection('aurora')->table('Email Campaign Dimension')->count();
    }
}

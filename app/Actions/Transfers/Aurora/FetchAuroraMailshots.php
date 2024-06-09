<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 10 Mar 2023 20:59:01 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Transfers\Aurora;

use App\Actions\Mail\Mailshot\StoreMailshot;
use App\Actions\Mail\Mailshot\UpdateMailshot;
use App\Models\Mail\Mailshot;
use App\Transfers\SourceOrganisationService;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use JetBrains\PhpStorm\NoReturn;

class FetchAuroraMailshots extends FetchAuroraAction
{
    public string $commandSignature = 'fetch:mailshots {organisations?*} {--s|source_id=}';


    #[NoReturn] public function handle(SourceOrganisationService $organisationSource, int $organisationSourceId): ?Mailshot
    {
        if ($shopData = $organisationSource->fetchMailshot($organisationSourceId)) {
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

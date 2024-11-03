<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 12 Aug 2024 11:55:49 Central Indonesia Time, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Transfers\Aurora;

use App\Actions\Accounting\CreditTransaction\StoreCreditTransaction;
use App\Actions\Accounting\CreditTransaction\UpdateCreditTransaction;
use App\Models\Accounting\CreditTransaction;
use App\Transfers\SourceOrganisationService;
use Exception;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Throwable;

class FetchAuroraCredits extends FetchAuroraAction
{
    public string $commandSignature = 'fetch:credits {organisations?*} {--s|source_id=} {--d|db_suffix=}';

    public function handle(SourceOrganisationService $organisationSource, int $organisationSourceId): ?CreditTransaction
    {
        if ($creditData = $organisationSource->fetchCredit($organisationSourceId)) {
            if ($creditTransaction = CreditTransaction::where('source_id', $creditData['credit']['source_id'])->first()) {
                try {
                    $creditTransaction = UpdateCreditTransaction::make()->action(
                        creditTransaction: $creditTransaction,
                        modelData: $creditData['credit'],
                        hydratorsDelay: 60,
                        strict: false,
                    );
                    $this->recordChange($organisationSource, $creditTransaction->wasChanged());
                } catch (Exception $e) {
                    $this->recordError($organisationSource, $e, $creditData['credit'], 'CreditTransaction', 'update');

                    return null;
                }
            } else {
                try {
                    $creditTransaction = StoreCreditTransaction::make()->action(
                        customer: $creditData['customer'],
                        modelData: $creditData['credit'],
                        hydratorsDelay: 60,
                        strict: false,
                    );


                    $this->recordNew($organisationSource);

                    $sourceData = explode(':', $creditTransaction->source_id);
                    DB::connection('aurora')->table('Credit Transaction Fact')
                        ->where('Credit Transaction Key', $sourceData[1])
                        ->update(['aiku_id' => $creditTransaction->id]);
                } catch (Exception|Throwable $e) {
                    $this->recordError($organisationSource, $e, $creditData['credit'], 'CreditTransaction', 'store');

                    return null;
                }
            }

            return $creditTransaction;
        }

        return null;
    }

    public function getModelsQuery(): Builder
    {
        return DB::connection('aurora')
            ->table('Credit Transaction Fact')
            ->select('Credit Transaction Key as source_id')
            ->orderBy('source_id');
    }

    public function count(): ?int
    {
        return DB::connection('aurora')->table('Credit Transaction Fact')->count();
    }


}

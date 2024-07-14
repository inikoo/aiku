<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 07 Feb 2023 19:01:11 Malaysia Time, Ubud, Bali
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Transfers\Aurora;

use App\Actions\CRM\CustomerClient\DeleteCustomerClient;
use App\Actions\Dispatching\DeliveryNote\DeleteDeliveryNote;
use App\Actions\Traits\WithOrganisationsArgument;
use App\Actions\Traits\WithOrganisationSource;
use App\Models\Dispatching\DeliveryNote;
use App\Models\Dropshipping\CustomerClient;
use App\Models\SysAdmin\Organisation;
use App\Transfers\SourceOrganisationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use JetBrains\PhpStorm\NoReturn;
use Lorisleiva\Actions\Concerns\AsAction;

class FetchFromStack
{
    use AsAction;
    use WithOrganisationsArgument;
    use WithOrganisationSource;
    use WithOrganisationSource;


    public string $commandSignature = 'fetch:stack {organisations?*} {--M|max_run_time=0 : max fun time in seconds}';
    protected float $startTime;
    protected ?float $maxRunTime = 0;

    #[NoReturn] public function handle(SourceOrganisationService $organisationSource): void
    {
        $query = DB::connection('aurora')
            ->table('pika_fetch');
        $query->orderBy('created_at');

        foreach ($query->get() as $jobData) {
            switch ($jobData->model) {
                case 'Customer':
                    $res = FetchAuroraCustomers::run($organisationSource, $jobData->model_id);
                    if (!$res) {
                        $res = FetchAuroraDeletedCustomers::run($organisationSource, $jobData->model_id);
                    }
                    break;
                case 'delete_customer':
                    $res = DeleteCustomerFromAurora::run($organisationSource, $jobData->model_id);
                    break;
                case 'CustomerClient':
                    $res = FetchAuroraCustomerClients::run($organisationSource, $jobData->model_id);
                    break;
                case 'Stock':
                    $res = FetchAuroraStocks::run($organisationSource, $jobData->model_id);
                    break;
                case 'Order':
                    $res = FetchAuroraOrders::run($organisationSource, $jobData->model_id);
                    break;
                case 'DeliveryNote':
                    $res = FetchAuroraDeliveryNotes::run($organisationSource, $jobData->model_id);
                    break;
                case 'Invoice':
                    $res = FetchAuroraInvoices::run($organisationSource, $jobData->model_id);

                    break;
                case 'Asset':
                    $res = FetchAuroraProducts::run($organisationSource, $jobData->model_id);
                    break;
                case 'Supplier':
                    $res = FetchAuroraSuppliers::run($organisationSource, $jobData->model_id);
                    break;
                case 'Agent':
                    $res = FetchAuroraAgents::run($organisationSource, $jobData->model_id);
                    break;
                case 'delete_invoice':
                    $res = DeleteInvoiceFromAurora::run($organisationSource, $jobData->model_id);
                    break;


                case 'delete_delivery_note':
                    $res = true;
                    if ($deliveryNote = DeliveryNote::where('source_id', $jobData->model_id)->first()) {
                        $res = DeleteDeliveryNote::run($deliveryNote);
                    }

                    break;
                case 'delete_customer_client':
                    $res = true;
                    if ($customerClient = CustomerClient::where('source_id', $jobData->model_id)->first()) {
                        $res = DeleteCustomerClient::run($customerClient);
                    }
                    break;
                default:
                    continue 2;
            }


            DB::connection('aurora')->table('pika_fetch')->where('id', $jobData->id)->delete();


            if ($res) {
                DB::connection('aurora')->table('pika_fetch_error')
                    ->where('model', $jobData->model)
                    ->where('model_id', $jobData->model_id)
                    ->delete();
            } else {
                DB::connection('aurora')->table('pika_fetch_error')->updateOrInsert(
                    [
                        'model'    => $jobData->model,
                        'model_id' => $jobData->model_id,
                    ],
                    [
                        'updated_at' => gmdate('Y-m-d H:i:s')

                    ]
                );
            }


            if ($this->maxRunTime > 0 and (microtime(true) - $this->startTime) > $this->maxRunTime) {
                return;
            }
        }
    }


    public function asCommand(Command $command): int
    {
        $this->startTime = microtime(true);

        $this->maxRunTime = $command->option('max_run_time');

        $organisations  = $this->getOrganisations($command);
        $exitCode       = 0;

        foreach ($organisations as $organisation) {
            $result = (int)$organisation->execute(
                /**
                 * @throws \Exception
                 */
                function (Organisation $organisation) use ($command) {
                    $organisationSource = $this->getOrganisationSource($organisation);
                    $organisationSource->initialisation($organisation);

                    $this->handle($organisationSource);
                }
            );

            if ($result !== 0) {
                $exitCode = $result;
            }
        }

        return $exitCode;
    }
}

<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 07 Feb 2023 19:01:11 Malaysia Time, Ubud, Bali
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\SourceFetch\Aurora;


use App\Actions\Delivery\DeliveryNote\DeleteDeliveryNote;
use App\Actions\Dropshipping\CustomerClient\DeleteCustomerClient;
use App\Actions\WithTenantsArgument;
use App\Managers\Tenant\SourceTenantManager;
use App\Models\Delivery\DeliveryNote;
use App\Models\Dropshipping\CustomerClient;
use App\Services\Tenant\SourceTenantService;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use JetBrains\PhpStorm\NoReturn;
use Lorisleiva\Actions\Concerns\AsAction;


class FetchFromStack
{
    use AsAction;
    use WithTenantsArgument;


    public string $commandSignature = 'fetch:stack {tenants?*} {--M|max_run_time=0 : max fun time in seconds}';
    protected float $startTime;
    protected ?float $maxRunTime = 0;

    #[NoReturn] public function handle(SourceTenantService $tenantSource): void
    {
        $query = DB::connection('aurora')
            ->table('pika_fetch');
        $query->orderBy('created_at');

        foreach ($query->get() as $jobData) {
            switch ($jobData->model) {
                case 'Customer':
                    $res = FetchCustomers::run($tenantSource, $jobData->model_id);
                    if (!$res) {
                        $res = FetchDeletedCustomers::run($tenantSource, $jobData->model_id);
                    }
                    break;
                case 'delete_customer':
                    $res = DeleteCustomerFromAurora::run($tenantSource, $jobData->model_id);
                    break;
                case 'CustomerClient':
                    $res = FetchCustomerClients::run($tenantSource, $jobData->model_id);
                    break;
                case 'Stock':
                    $res = FetchStocks::run($tenantSource, $jobData->model_id);
                    break;
                case 'Order':
                    $res = FetchOrders::run($tenantSource, $jobData->model_id);
                    break;
                case 'DeliveryNote':
                    $res = FetchDeliveryNotes::run($tenantSource, $jobData->model_id);
                    break;
                case 'Invoice':
                    $res = FetchInvoices::run($tenantSource, $jobData->model_id);

                    break;
                case 'Product':
                    $res = FetchProducts::run($tenantSource, $jobData->model_id);
                    break;
                case 'Supplier':
                    $res = FetchSuppliers::run($tenantSource, $jobData->model_id);
                    break;
                case 'Agent':
                    $res = FetchAgents::run($tenantSource, $jobData->model_id);
                    break;
                case 'delete_invoice':
                    $res = DeleteInvoiceFromAurora::run($tenantSource, $jobData->model_id);
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

        $tenants  = $this->getTenants($command);
        $exitCode = 0;

        foreach ($tenants as $tenant) {
            $result = (int)$tenant->run(
            /**
             * @throws \Illuminate\Contracts\Container\BindingResolutionException
             */ function () use ($command) {
                $tenantSource = app(SourceTenantManager::class)->make(Arr::get(tenant()->source, 'type'));
                $tenantSource->initialisation(tenant());

                $this->handle($tenantSource);
            }
            );

            if ($result !== 0) {
                $exitCode = $result;
            }
        }

        return $exitCode;
    }


}

<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Mon, 05 Sept 2022 00:35:52 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\SourceFetch\Aurora;

use App\Actions\WithTenantsArgument;
use App\Actions\WithTenantSource;
use App\Models\Marketing\Shop;
use App\Models\Tenancy\Tenant;
use App\Services\Tenant\SourceTenantService;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Arr;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Symfony\Component\Console\Helper\ProgressBar;

class FetchAction
{
    use AsAction;
    use WithTenantsArgument;
    use WithTenantSource;

    protected int $counter = 0;

    protected ?ProgressBar $progressBar;
    protected ?Shop $shop;
    protected array $with;
    protected bool $onlyNew = false;
    private ?Tenant $tenant;

    protected int $hydrateDelay = 0;

    public function __construct()
    {
        $this->progressBar = null;
        $this->shop        = null;
        $this->with        = [];
    }

    public function handle(SourceTenantService $tenantSource, int $tenantSourceId): ?Model
    {
        return null;
    }

    public function getModelsQuery(): ?Builder
    {
        return null;
    }

    public function fetchAll(SourceTenantService $tenantSource, Command $command = null): void
    {
        $this->getModelsQuery()->chunk(10000, function ($chunkedData) use ($command, $tenantSource) {
            foreach ($chunkedData as $auroraData) {
                if ($command && $command->getOutput()->isDebug()) {
                    $command->line("Fetching: ".$auroraData->{'source_id'});
                }
                $model = $this->handle($tenantSource, $auroraData->{'source_id'});
                unset($model);
                $this->progressBar?->advance();
            }
        });
    }

    public function count(): ?int
    {
        return null;
    }

    public function reset(): void
    {
    }

    public function asCommand(Command $command): int
    {
        $this->hydrateDelay = 120;

        $tenants  = $this->getTenants($command);
        $exitCode = 0;


        foreach ($tenants as $tenant) {
            $result = (int)$tenant->execute(function () use ($command, $tenant) {
                if (in_array($command->getName(), ['fetch:customers', 'fetch:web-users', 'fetch:products']) and $command->option('shop')) {
                    $this->shop = Shop::where('slug', $command->option('shop'))->firstOrFail();
                }

                if (in_array($command->getName(), [
                    'fetch:stocks',
                    'fetch:products',
                    'fetch:orders',
                    'fetch:invoices',
                    'fetch:customers',
                    'fetch:delivery-notes',
                    'fetch:purchase-orders'

                ])) {
                    $this->onlyNew = (bool)$command->option('only_new');
                }

                if (in_array($command->getName(), ['fetch:customers', 'fetch:orders', 'fetch:invoices', 'fetch:delivery-notes'])) {
                    $this->with = $command->option('with');
                }

                try {
                    $tenantSource = $this->getTenantSource($tenant);
                } catch (Exception $exception) {
                    $command->error($exception->getMessage());

                    return 1;
                }

                $tenantSource->initialisation(app('currentTenant'), $command->option('db_suffix') ?? '');

                if (in_array($command->getName(), [
                        'fetch:stocks',
                        'fetch:products',
                        'fetch:orders',
                        'fetch:invoices',
                        'fetch:customers',
                        'fetch:web-users',
                        'fetch:delivery-notes',
                        'fetch:purchase-orders'
                    ]) and $command->option('reset')) {
                    $this->reset();
                }

                $command->info('');

                if ($command->option('source_id')) {
                    $this->handle($tenantSource, $command->option('source_id'));
                } else {
                    if (!$command->option('quiet') and !$command->getOutput()->isDebug()) {
                        $info = '✊ '.$command->getName().' '.$tenant->slug;
                        if ($this->shop) {
                            $info .= ' shop:'.$this->shop->slug;
                        }
                        $command->line($info);
                        $this->progressBar = $command->getOutput()->createProgressBar($this->count() ?? 0);
                        $this->progressBar->setFormat('debug');
                        $this->progressBar->start();
                    } else {
                        $command->line('Steps '.number_format($this->count()));
                    }

                    $this->fetchAll($tenantSource, $command);
                    $this->progressBar?->finish();
                }

                return 0;
            });

            if ($result !== 0) {
                $exitCode = $result;
            }
        }

        return $exitCode;
    }


    public function authorize(ActionRequest $request): bool
    {
        if ($request->user()->userable_type == 'Tenant') {
            $this->tenant = $request->user()->tenant;

            if ($this->tenant->id and $request->user()->tokenCan('aurora')) {
                return true;
            }
        }

        return false;
    }

    public function rules(): array
    {
        return [
            'id' => ['sometimes'],
        ];
    }


    public function asController(ActionRequest $request)
    {
        $validatedData = $request->validated();

        return $this->tenant->execute(
            /**
             * @throws \Exception
             */
            function (Tenant $tenant) use ($validatedData) {
                $tenantSource = $this->getTenantSource($tenant);
                $tenantSource->initialisation(app('currentTenant'));

                return $this->handle($tenantSource, Arr::get($validatedData, 'id'));
            }
        );
    }

    public function jsonResponse($model): array
    {
        if ($model) {
            return [
                'model'     => $model->getMorphClass(),
                'id'        => $model->id,
                'source_id' => $model->source_id,
            ];
        } else {
            return [
                'error' => 'model not returned'
            ];
        }
    }
}

<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Mon, 05 Sept 2022 00:35:52 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */


namespace App\Actions\SourceFetch\Aurora;

use App\Actions\WithTenantsArgument;
use App\Jobs\Middleware\InitialiseSourceTenant;
use App\Managers\Tenant\SourceTenantManager;
use App\Services\Tenant\SourceTenantService;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Arr;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Decorators\JobDecorator;
use Symfony\Component\Console\Helper\ProgressBar;


class FetchAction
{
    use AsAction;
    use WithTenantsArgument;

    protected  Int $counter=0;

    protected ?ProgressBar $progressBar;

    public function __construct()
    {
        $this->progressBar = null;

    }

    public function handle(SourceTenantService $tenantSource, int $tenantSourceId): ?Model
    {
        return null;
    }

    function getModelsQuery(): ?Builder
    {
        return null;
    }

    public function fetchAll(SourceTenantService $tenantSource): void
    {

        foreach ($this->getModelsQuery()->get() as $auroraData) {
            $this->handle($tenantSource, $auroraData->{'source_id'});
            $this->progressBar?->advance();
        }
    }

    public function fetchSome(SourceTenantService $tenantSource, array $tenantIds): void
    {
        foreach ($tenantIds as $sourceId) {
            $this->handle($tenantSource, $sourceId);
        }
    }

    public function count(): ?int
    {
        return null;
    }


    /**
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function asCommand(Command $command): int
    {
        $tenants  = $this->getTenants($command);
        $exitCode = 0;

        foreach ($tenants as $tenant) {
            $result = (int)$tenant->run(function () use ($command,$tenant) {
                $tenantSource = app(SourceTenantManager::class)->make(Arr::get(tenant()->source, 'type'));
                $tenantSource->initialisation(tenant());
                $command->info('');

                if ($command->option('source_id')) {
                    $this->handle($tenantSource, $command->option('source_id'));
                } else {

                    if(!$command->option('quiet')) {
                        $this->progressBar = $command->getOutput()->createProgressBar($this->count());
                        $this->progressBar->setFormat('debug');
                        $this->progressBar->start();
                    }else{
                        $command->line('sss '.$this->count());
                    }

                    $this->fetchAll($tenantSource);
                    $this->progressBar?->finish();

                }
            });

            if ($result !== 0) {
                $exitCode = $result;
            }
        }

        return $exitCode;
    }

    public function asJob(SourceTenantService $tenantSource, ?array $tenantIds = null): void
    {
        if (is_array($tenantIds)) {
            $this->fetchSome($tenantSource, $tenantIds);
        } else {
            $this->getModelsQuery()
                ->chunk(100, function ($tenantIds) use ($tenantSource) {
                    $this->dispatch($tenantSource, $tenantIds->pluck('source_id')->all());
                });
        }
    }

    public function getJobMiddleware(): array
    {
        return [new InitialiseSourceTenant()];
    }

    public function configureJob(JobDecorator $job): void
    {
        $job->onQueue('fetches')
            ->setTries(5)
            ->setMaxExceptions(3)
            ->setTimeout(1800);
    }

}


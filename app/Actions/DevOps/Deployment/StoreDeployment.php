<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 23 Apr 2023 11:33:58 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\DevOps\Deployment;

use App\Http\Resources\DevOps\DeploymentResource;
use App\Models\DevOps\Deployment;
use Exception;
use Illuminate\Console\Command;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;
use PHLAK\SemVer\Version;
use Symfony\Component\Process\Exception\RuntimeException;
use Symfony\Component\Process\Process;

class StoreDeployment
{
    use AsAction;
    use WithAttributes;


    private null|Deployment $latestDeployment=null;


    /**
     * @throws \PHLAK\SemVer\Exceptions\InvalidVersionException
     */
    public function handle(string $currentHash): Deployment
    {
        $data = [
            'changes' => [
                'repo'     => false,
                'vendors'  => false,
                'npm'      => false,
                'frontend' => false,
            ]
        ];

        $modelData=[
            'hash'    => $currentHash,
        ];

        if($this->latestDeployment) {

            $filesChanged = $this->runGitCommand("git --git-dir ".config('deployments.repo_path')."   diff --name-only $currentHash $this->latestDeployment->hash");

            if ($currentHash != $this->latestDeployment->hash) {
                $data['changes']['repo'] = true;
            }

            if (preg_match('/composer\.lock/', $filesChanged)) {
                $data['changes']['vendors'] = true;
            }
            if (preg_match('/package\.lock/', $filesChanged)) {
                $data['changes']['npm'] = true;
            }

            if (str_contains($filesChanged, 'resources')) {
                $data['changes']['frontend'] = true;
            }


            $version = Version::parse($this->latestDeployment->version);

            if ($currentHash == $this->latestDeployment->hash) {
                $build = (int)$version->build ?? 0;
                $build++;

                $version->setBuild(sprintf('%03d', $build));
            } else {
                $version->incrementPatch();
            }

            $modelData['data']=$data;

        } else {
            $version           = new Version();
            $modelData['state']='deployed';

        }
        $modelData['version']=$version;

        return Deployment::create($modelData);

    }

    public function prepareForValidation(): void
    {

        if($this->latestDeployment) {
            $this->fill([
                'latest_hash' => $this->latestDeployment->hash,
            ]);
        }

    }

    public function rules(): array
    {
        return [
            'current_hash' => ['required', 'regex:/^[0-9a-f]{7,40}$/i'],
            'latest_hash'  => ['sometimes', 'required', 'regex:/^[0-9a-f]{7,40}$/i'],
        ];
    }

    public string $commandSignature = 'create:deployment';

    public function getCommandDescription(): string
    {
        return 'Create deployment.';
    }

    /**
     * @throws \PHLAK\SemVer\Exceptions\InvalidVersionException
     */
    public function asCommand(Command $command): int
    {
        $this->latestDeployment = Deployment::latest()->first();

        $this->fill([
            'current_hash' => $this->getCurrentHash(),
        ]);
        try {
            $this->validateAttributes();
        } catch (Exception $e) {
            $command->error($e->getMessage());

            return 1;
        }

        $deployment=$this->handle($this->getCurrentHash());
        $command->line("Deployment created $deployment->version");
        return 0;
    }

    /**
     * @throws \PHLAK\SemVer\Exceptions\InvalidVersionException
     */
    public function asController(): Deployment
    {
        $this->latestDeployment = Deployment::latest()->first();

        $this->fill([
            'current_hash' => $this->getCurrentHash(),
        ]);
        $this->validateAttributes();

        return $this->handle($this->getCurrentHash());

    }

    public function jsonResponse($deployment): DeploymentResource
    {
        return new DeploymentResource($deployment);
    }


    public function getCurrentHash(): string
    {
        return $this->runGitCommand('git --git-dir '.config('deployments.repo_path').' describe --always');
    }

    public function runGitCommand($command): string
    {
        try {
            if (method_exists(Process::class, 'fromShellCommandline')) {
                $process = Process::fromShellCommandline($command);
            } else {
                $process = new Process([$command]);
            }

            $process->mustRun();

            return trim($process->getOutput());
        } catch (RuntimeException) {
            return '';
        }
    }


}

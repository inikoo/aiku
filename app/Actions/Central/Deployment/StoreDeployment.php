<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 04 Oct 2022 11:28:53 Central European Summer Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\Central\Deployment;

use App\Models\Central\Deployment;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Lorisleiva\Actions\Concerns\AsAction;
use PHLAK\SemVer\Version;
use Symfony\Component\Process\Exception\RuntimeException;
use Symfony\Component\Process\Process;
use Throwable;

class StoreDeployment
{
    use AsAction;

    public string $commandSignature = 'create:deployment';

    public function getCommandDescription(): string
    {
        return 'Create deployment.';
    }

    public function handle(array $modelData): Deployment
    {
        return Deployment::create($modelData);
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

    /**
     * @throws \PHLAK\SemVer\Exceptions\InvalidVersionException
     */
    public function asController(): Model|Deployment|JsonResponse
    {
        $data = [
            'changes' => [
                'repo'     => false,
                'vendors'  => false,
                'npm'      => false,
                'frontend' => false,
            ]
        ];


        $currentHash = $this->getCurrentHash();
        if ($latestDeployment = Deployment::latest()->first()) {
            $latestHash = $latestDeployment->hash;


            if (!$this->validateHash($currentHash) or !$this->validateHash($latestHash)) {
                return response()->json([
                                            'msg' => "Invalid hash $currentHash or $latestHash",

                                        ], 400);
            } else {
                $filesChanged = $this->runGitCommand("git --git-dir ".config('deployments.repo_path')."   diff --name-only $currentHash $latestHash");

                if ($currentHash != $latestHash) {
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
            }

            $version = Version::parse($latestDeployment->version);

            if ($currentHash == $latestHash) {
                $build = (int)$version->build ?? 0;
                $build++;

                $version->setBuild(sprintf('%03d', $build));
            } else {
                $version->incrementPatch();
            }
        } else {
            $version = new Version();
        }

        try {
            return Deployment::create([
                                          'version' => (string)$version,
                                          'hash'    => $currentHash,
                                          'data'    => $data
                                      ]);
        } catch (Throwable $e) {
            report($e);

            return response()->json(
                [
                    'message' => 'Error creating the deployment.'
                ],
                404
            );
        }
    }


    private function validateHash($hash): bool
    {
        return preg_match('/^[0-9a-f]{7,40}$/', $hash) == true;
    }
}

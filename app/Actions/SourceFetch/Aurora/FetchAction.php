<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Mon, 05 Sept 2022 00:35:52 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\SourceFetch\Aurora;

use App\Actions\SysAdmin\User\UpdateUser;
use App\Actions\Traits\WithOrganisationsArgument;
use App\Actions\Traits\WithOrganisationSource;
use App\Models\SysAdmin\Organisation;
use App\Models\Market\Shop;
use App\Services\Organisation\SourceOrganisationService;
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
    use WithOrganisationsArgument;
    use WithOrganisationSource;

    protected int $counter = 0;

    protected ?ProgressBar $progressBar;
    protected ?Shop $shop;
    protected array $with;
    protected bool $onlyNew = false;

    protected int $hydrateDelay = 0;

    public function __construct()
    {
        $this->progressBar = null;
        $this->shop        = null;
        $this->with        = [];
    }

    public function handle(SourceOrganisationService $organisationSource, int $organisationSourceId): ?Model
    {
        return null;
    }

    public function getModelsQuery(): ?Builder
    {
        return null;
    }

    public function fetchAll(SourceOrganisationService $organisationSource, Command $command = null): void
    {
        $this->getModelsQuery()->chunk(10000, function ($chunkedData) use ($command, $organisationSource) {
            foreach ($chunkedData as $auroraData) {
                if ($command && $command->getOutput()->isDebug()) {
                    $command->line("Fetching: ".$auroraData->{'source_id'});
                }
                $model = $this->handle($organisationSource, $auroraData->{'source_id'});
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

        $organisations = $this->getOrganisations($command);
        $exitCode      = 0;


        foreach ($organisations as $organisation) {
            $result = $this->processOrganisation($command, $organisation);

            if ($result !== 0) {
                $exitCode = $result;
            }
        }

        return $exitCode;
    }

    public function processOrganisation(Command $command, Organisation $organisation): int
    {
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
            'fetch:purchase-orders',
            'fetch:suppliers'

        ])) {
            $this->onlyNew = (bool)$command->option('only_new');
        }

        if (in_array($command->getName(), ['fetch:customers', 'fetch:orders', 'fetch:invoices', 'fetch:delivery-notes'])) {
            $this->with = $command->option('with');
        }

        try {
            $organisationSource = $this->getOrganisationSource($organisation);
        } catch (Exception $exception) {
            $command->error($exception->getMessage());

            return 1;
        }

        $organisationSource->initialisation($organisation, $command->option('db_suffix') ?? '');

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
            $this->handle($organisationSource, $command->option('source_id'));
        } else {
            if (!$command->option('quiet') and !$command->getOutput()->isDebug()) {
                $info = '✊ '.$command->getName().' '.$organisation->slug;
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

            $this->fetchAll($organisationSource, $command);
            $this->progressBar?->finish();
        }

        return 0;

    }


    public function authorize(ActionRequest $request): bool
    {
        if ($request->user()->userable_type == 'Organisation') {
            $organisation = $request->user()->organisation;

            if ($organisation->id and $request->user()->tokenCan('aurora')) {
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


    /**
     * @throws \Exception
     */
    public function asController(Organisation $organisation, ActionRequest $request): ?Model
    {
        $validatedData = $request->validated();


        $organisationSource = $this->getOrganisationSource($organisation);
        $organisationSource->initialisation($organisation);

        return $this->handle($organisationSource, Arr::get($validatedData, 'id'));
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

    public function saveImage($model, $imageData, $imageField = 'image_id', $mediaCollection = 'photo'): void
    {
        if (array_key_exists('image_path', $imageData) and file_exists($imageData['image_path'])) {
            $image_path = $imageData['image_path'];
            $filename   = $imageData['filename'];
            $checksum   = md5_file($image_path);

            if ($model->getMedia($mediaCollection, ['checksum' => $checksum])->count() == 0) {
                /** @var \App\Models\Media\Media $media */
                $model->update([$imageField => null]);

                $media = $model->addMedia($image_path)
                    ->preservingOriginal()
                    ->withCustomProperties(['checksum' => $checksum])
                    ->usingName($filename)
                    ->usingFileName($checksum.".".pathinfo($image_path, PATHINFO_EXTENSION))
                    ->toMediaCollection($mediaCollection, 'group');
                if (class_basename($model) == 'GroupUser') {
                    UpdateUser::run(
                        $model,
                        [
                            'avatar_id' => $media->id
                        ]
                    );
                } else {
                    $model->update([$imageField => $media->id]);
                }
            }
        }
    }

}

<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Mon, 05 Sept 2022 00:35:52 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\SourceFetch\Aurora;

use App\Actions\Helpers\Fetch\StoreFetch;
use App\Actions\Helpers\Fetch\UpdateFetch;
use App\Actions\Media\Media\UpdateIsAnimatedMedia;
use App\Actions\SysAdmin\User\UpdateUser;
use App\Actions\Traits\WithOrganisationsArgument;
use App\Actions\Traits\WithOrganisationSource;
use App\Enums\Helpers\Fetch\FetchTypeEnum;
use App\Enums\Helpers\FetchRecord\FetchRecordTypeEnum;
use App\Models\Market\Shop;
use App\Models\Media\Media;
use App\Models\SupplyChain\Agent;
use App\Models\SupplyChain\Supplier;
use App\Models\SysAdmin\Organisation;
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
    protected bool $onlyNew  = false;
    protected bool $fetchAll = false;

    protected int $number_stores     = 0;
    protected int $number_updates    = 0;
    protected int $number_no_changes = 0;
    protected int $number_errors     = 0;

    protected $organisationSource = null;

    protected int $hydrateDelay = 0;

    public function __construct()
    {
        $this->progressBar = null;
        $this->shop        = null;
        $this->with        = [];
    }

    public function handle(SourceOrganisationService $organisationSource, int $organisationSourceId): Model|array|null
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

        if ($command->getName() == 'fetch:webpages') {
            $this->fetchAll = (bool)$command->option('all');
        }


        if (in_array($command->getName(), [
                'fetch:customers',
                'fetch:web-users',
                'fetch:products',
                'fetch:webpages',
                'fetch:invoices',
                'fetch:orders',
                'fetch:delivery-notes',
                'fetch:products',
                'fetch:services'
            ]) and $command->option('shop')) {
            $this->shop = Shop::where('slug', $command->option('shop'))->firstOrFail();
        }

        if (in_array($command->getName(), [
            'fetch:stocks',
            'fetch:products',
            'fetch:orders',
            'fetch:invoices',
            'fetch:customers',
            'fetch:customer-clients',
            'fetch:delivery-notes',
            'fetch:purchase-orders',
            'fetch:suppliers',
            'fetch:web-users',
            'fetch:prospects',
            'fetch:deleted-customers',
            'fetch:webpages',
            'fetch:supplier-products',
            'fetch:payments',
            'fetch:pallets',
            'fetch:families'
        ])) {
            $this->onlyNew = (bool)$command->option('only_new');
        }


        if (in_array($command->getName(), ['fetch:customers', 'fetch:orders', 'fetch:invoices', 'fetch:delivery-notes'])) {
            $this->with = $command->option('with');
        }

        try {
            $this->organisationSource = $this->getOrganisationSource($organisation);
        } catch (Exception $exception) {
            $command->error($exception->getMessage());

            return 1;
        }
        $this->organisationSource->initialisation($organisation, $command->option('db_suffix') ?? '');

        $this->organisationSource->fetch = StoreFetch::run(
            [
                'type' => $this->getFetchType($command),
                'data' => [
                    'command'   => $command->getName(),
                    'arguments' => $command->arguments(),
                    'options'   => $command->options(),
                ]
            ]
        );


        if (in_array($command->getName(), [
                'fetch:stocks',
                'fetch:products',
                'fetch:orders',
                'fetch:invoices',
                'fetch:customers',
                'fetch:customers-clients',
                'fetch:web-users',
                'fetch:delivery-notes',
                'fetch:purchase-orders',
                'fetch:web-users',
                'fetch:prospects',
                'fetch:deleted-customers',
                'fetch:webpages'
            ]) and $command->option('reset')) {
            $this->reset();
        }

        $command->info('');

        if ($command->option('source_id')) {
            $this->handle($this->organisationSource, $command->option('source_id'));
            UpdateFetch::run($this->organisationSource->fetch, ['number_items' => 1]);
        } else {
            $numberItems = $this->count() ?? 0;
            UpdateFetch::run($this->organisationSource->fetch, ['number_items' => $numberItems]);
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

            $this->fetchAll($this->organisationSource, $command);
            $this->progressBar?->finish();
        }
        UpdateFetch::run($this->organisationSource->fetch, ['finished_at' => now()]);

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


        $this->organisationSource = $this->getOrganisationSource($organisation);
        $this->organisationSource->initialisation($organisation);

        return $this->handle($this->organisationSource, Arr::get($validatedData, 'id'));
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

    /**
     * @throws \Spatie\MediaLibrary\MediaCollections\Exceptions\FileDoesNotExist
     * @throws \Spatie\MediaLibrary\MediaCollections\Exceptions\FileIsTooBig
     */
    public function saveImage(Agent|Supplier $model, $imageData, $imageField = 'image_id', $mediaCollection = 'photo'): void
    {
        if (array_key_exists('image_path', $imageData) and file_exists($imageData['image_path'])) {
            $imagePath  = $imageData['image_path'];
            $filename   = $imageData['filename'];
            $checksum   = md5_file($imagePath);

            if ($model->getMedia($mediaCollection, ['checksum' => $checksum])->count() == 0) {

                $model->update([$imageField => null]);
                /** @var Media $media */
                $media = $model->addMedia($imagePath)
                    ->preservingOriginal()
                    ->withProperties(
                        [
                            'checksum' => $checksum,
                            'group_id' => $model->group_id
                        ]
                    )
                    ->usingName($filename)
                    ->usingFileName($checksum.".".pathinfo($imagePath, PATHINFO_EXTENSION))
                    ->toMediaCollection($mediaCollection);

                $media->refresh();
                UpdateIsAnimatedMedia::run($media, $imagePath);

                if (class_basename($model) == 'User') {
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

    private function getFetchType(Command $command): ?FetchTypeEnum
    {
        return match ($command->getName()) {
            'fetch:prospects' => FetchTypeEnum::PROSPECTS,
            default           => FetchTypeEnum::BASE,
        };
    }

    public function recordError($organisationSource, $e, $modelData, $modelType = null, $errorOn = null): void
    {
        $this->number_errors++;
        UpdateFetch::run($organisationSource->fetch, ['number_errors' => $this->number_errors]);
        $organisationSource->fetch->records()->create([
            'model_data' => $modelData,
            'data'       => $e->getMessage(),
            'type'       => FetchRecordTypeEnum::ERROR,
            'source_id'  => $modelData['source_id'],
            'model_type' => $modelType,
            'error_on'   => $errorOn
        ]);
        print_r($modelData);
        print_r($e->getMessage());
    }

    public function recordChange($organisationSource, $wasChanged): void
    {
        if ($wasChanged) {
            $this->number_updates++;
            UpdateFetch::run($organisationSource->fetch, ['number_updates' => $this->number_updates]);
        } else {
            $this->number_no_changes++;
            UpdateFetch::run($organisationSource->fetch, ['number_no_changes' => $this->number_no_changes]);
        }
    }

    public function recordNew($organisationSource): void
    {
        $this->number_stores++;
        UpdateFetch::run($organisationSource->fetch, ['number_stores' => $this->number_stores]);
    }

}

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
use App\Actions\SourceFetch\FetchAction;
use App\Actions\SysAdmin\User\UpdateUser;
use App\Enums\Helpers\Fetch\FetchTypeEnum;
use App\Models\Market\Shop;
use App\Models\Media\Media;
use App\Models\SupplyChain\Agent;
use App\Models\SupplyChain\Supplier;
use App\Models\SysAdmin\Organisation;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Lorisleiva\Actions\ActionRequest;

class FetchAuroraAction extends FetchAction
{
    use WithAuroraOrganisationsArgument;


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
                'fetch:outers',
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
            'fetch:families',
            'fetch:outers',
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


}

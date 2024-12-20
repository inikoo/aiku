<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 21 Dec 2024 01:37:23 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Web\Webpage;

use App\Actions\Traits\WithEnumStats;
use App\Models\Catalogue\ProductCategory;
use App\Models\Web\Webpage;
use Exception;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Lorisleiva\Actions\Concerns\AsAction;

class SyncLinkedWebpages
{
    use AsAction;
    use WithEnumStats;

    private Webpage $webpage;

    public function __construct(Webpage $webpage)
    {
        $this->webpage = $webpage;
    }

    public function getJobMiddleware(): array
    {
        return [(new WithoutOverlapping($this->webpage->id))->dontRelease()];
    }

    public function handle(Webpage $webpage): void
    {
        $children = [];
        foreach ($webpage->webBlocks as $webBlock) {
            $webBlockTypeCode = $webBlock->webBlockType->code;
            switch ($webBlockTypeCode) {
                case 'department':
                    $departmentId = Arr::get($webBlock->layout, 'data.fieldValue.value.department_id');
                    /** @var ProductCategory $department */
                    $department = ProductCategory::find($departmentId);
                    if ($department) {
                        foreach ($department->getFamilies() as $family) {
                            $linkedWebpage = $family->webpage;
                            if ($linkedWebpage) {
                                $children[$linkedWebpage->id] = [
                                    'model_type'          => $linkedWebpage->model_type,
                                    'model_id'            => $linkedWebpage->model_id,
                                    'model_type_scope'    => 'department',
                                    'web_block_id'        => $webBlock->id,
                                    'web_block_type_code' => $webBlockTypeCode
                                ];
                            }
                        }
                    }
                    break;
                case 'family':

                    $familyId = Arr::get($webBlock->layout, 'data.fieldValue.value.family_id');
                    /** @var ProductCategory $family */
                    $family = ProductCategory::find($familyId);
                    if ($family) {
                        foreach ($family->getProducts() as $product) {
                            $linkedWebpage = $product->webpage;
                            if ($linkedWebpage) {
                                $children[$linkedWebpage->id] = [
                                    'model_type'          => $webpage->model_type,
                                    'model_id'            => $webpage->model_id,
                                    'model_type_scope'    => 'family',
                                    'web_block_id'        => $webBlock->id,
                                    'web_block_type_code' => $webBlockTypeCode
                                ];
                            }
                        }
                    }
                    break;

                default:
            }
        }

        $webpage->linkedWebpages()->sync($children);
    }

    public string $commandSignature = "webpage:sync_linked_webpages {webpage?}";

    /**
     * @throws \Exception
     */
    public function asCommand($command): int
    {
        if ($command->argument("webpage")) {
            try {
                /** @var Webpage $webpage */
                $webpage = Webpage::where("slug", $command->argument("webpage"))->firstOrFail();
            } catch (Exception) {
                $command->error("Webpage not found");
                exit();
            }
            $command->line("Webpage ".$webpage->slug." linked webpages sync");
            $this->handle($webpage);
        } else {
            $bar = $command->getOutput()->createProgressBar(Webpage::count());
            $bar->setFormat('debug');
            $bar->start();

            Webpage::withTrashed()->chunk(1000, function (Collection $models) use ($bar) {
                foreach ($models as $model) {
                    $this->handle($model);
                    $bar->advance();
                }
            });

            $bar->finish();
            $command->info("");
        }


        return 0;
    }


}

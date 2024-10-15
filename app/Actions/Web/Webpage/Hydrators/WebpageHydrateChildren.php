<?php
/*
 * Author: Ganes <gustiganes@gmail.com>
 * Created on: 15-10-2024, Bali, Indonesia
 * Github: https://github.com/aqordeon
 * Copyright: 2024
 *
*/

namespace App\Actions\Web\Webpage\Hydrators;

use App\Actions\Traits\WithEnumStats;
use App\Enums\Web\Webpage\WebpageChildrenScopeEnum;
use App\Models\Catalogue\ProductCategory;
use App\Models\Web\Webpage;
use Exception;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Support\Arr;
use Lorisleiva\Actions\Concerns\AsAction;

class WebpageHydrateChildren
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
        $childrenFamily = [];
        foreach ($webpage->webBlocks as $webBlock) {
            $weblockType = $webBlock->webBlockType->code;
            switch ($weblockType) {
                case 'department':
                    $departmentId = Arr::get($webBlock->layout, 'fieldValue.value.department_id');
                    $department = ProductCategory::findOrFail($departmentId);
                    if ($department) {
                        foreach ($department->getFamilies() as $family) {
                            $webpage = $family->webpage;
                            if ($webpage) {
                                $webpageId = $family->webpage->id;
                                $children[$webpageId] = $webpageId;
                            }
                        }
                    }
                    break;
                case 'family':

                    $familyId = Arr::get($webBlock->layout, 'fieldValue.value.family_id');
                    $family = ProductCategory::findOrFail($familyId);
                    if ($family) {
                        foreach ($family->products as $product) {
                            $webpage = $product->webpage;
                            if ($webpage) {
                                $webpageId = $product->webpage->id;
                                $childrenFamily[$webpageId] = $webpageId;
                            }
                        }
                    }
                    break;

                default:
            }
        }

        if (count($children) > 0) {
            foreach ($children as $childId) {
                $webpage = Webpage::find($childId);
                if ($webpage) {
                    $webpage->children()->attach($childId, ['model_type' => $webpage->model_type, 'model_id' => $webpage->model_id, 'scope' => WebpageChildrenScopeEnum::DEPARTMENT->value]);
                }
            }
        }

        if (count($childrenFamily) > 0) {
            foreach ($childrenFamily as $childId) {
                $webpage = Webpage::find($childId);
                if ($webpage) {
                    $webpage->children()->attach($childId, ['model_type' => $webpage->model_type, 'model_id' => $webpage->model_id, 'scope' => WebpageChildrenScopeEnum::FAMILY->value]);
                }
            }
        }

    }

    public string $commandSignature = "webpages:children {webpage?} {--d}";

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
            $command->line("Webpage ".$webpage->slug." children sync");
            $this->handle($webpage);

        } else {
            foreach (Webpage::orderBy('id')->get() as $webpage) {
                $command->line("Webpage ".$webpage->slug." children sync");
                $this->handle($webpage);
            }
        }



        return 0;
    }


}

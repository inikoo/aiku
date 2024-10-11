<?php
/*
 * author Arya Permana - Kirin
 * created on 09-10-2024-11h-39m
 * github: https://github.com/KirinZero0
 * copyright 2024
 */

namespace App\Actions\Traits\WebBlocks;

use App\Models\Web\WebBlockType;
use App\Transfers\Aurora\WithAuroraParsers;
use Illuminate\Support\Arr;
use Lorisleiva\Actions\Concerns\AsAction;

trait WithFetchDepartmentWebBlock
{
    use AsAction;
    use WithAuroraParsers;
    public function processDepartmentData(
        array &$models,
        $webpage,
        WebBlockType $webBlockType,
        $auroraBlock
    ): array|null {
        if (!isset($auroraBlock["type"])) {
            return null;
        }

        $layout = Arr::only($webBlockType->toArray(), ["code", "data", "name"]);
        $sections = [];
        foreach ($auroraBlock["sections"] as $section) {
            $sections[] = [
                "title" => $section["title"] ?? "",
                "subtitle" => $section["subtitle"] ?? "",
                "items" => array_filter(
                    array_map(function ($item) use ($webpage, &$models) {
                        if ($item["type"] == "category") {
                            $family = $this->parseFamily(
                                $webpage->organisation->id . ":" . $item["category_key"]
                            );
                            if ($family) {
                                $models[] = $family;
                            } else {
                                $department = $this->parseDepartment(
                                    $webpage->organisation->id . ":" . $item["category_key"]
                                );
                                if ($department) {
                                    $models[] = $department;
                                }
                            }

                            return [
                                "type" => $item["type"],
                                "category_id" => $family->id ?? $department->id,
                            ];
                        }
                        if ($item["type"] == "image") {
                            return [
                                "type" => $item["type"],
                                "aurora_source" => $item["image_src"],
                            ];
                        }
                        return $item;
                    }, $section["items"] ?? [])
                ),
            ];
        }

        data_set($layout, "data.fieldValue.value.sections", $sections);
        return $layout;
    }
}

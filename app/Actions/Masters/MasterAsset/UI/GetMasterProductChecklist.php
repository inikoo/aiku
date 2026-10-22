<?php

/*
 * Author: Rifqi <rifqitaufiqurrohman1@gmail.com>
 * Created: Thu, 15 Oct 2026
 * Copyright (c) 2026
 */

namespace App\Actions\Masters\MasterAsset\UI;

use App\Models\Masters\MasterAsset;
use App\Services\QueryBuilder;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;
use Lorisleiva\Actions\Concerns\AsObject;

/**
 * The product information a master product should carry, written once on the master for all the
 * shops. The items are SQL expressions over master_assets, so table rows and the "incomplete
 * information" count share one definition. The webpage checks belong to each shop's product, see
 * GetProductChecklist.
 */
class GetMasterProductChecklist
{
    use AsObject;

    /**
     * A shop product carries the same information, so GetProductChecklist reads these items from
     * products.
     *
     * @return array<string, array{label: string, filled: string}>
     */
    public function items(string $table = 'master_assets', string $modelType = 'MasterAsset'): array
    {
        return [
            'description'           => [
                'label'  => __('Description'),
                'filled' => "coalesce(trim({$table}.description), '') <> ''",
            ],
            'description_extra'     => [
                'label'  => __('Extra description'),
                'filled' => "coalesce(trim({$table}.description_extra), '') <> ''",
            ],
            'price'                 => [
                'label'  => __('Price'),
                'filled' => "coalesce({$table}.price, 0) > 0",
            ],
            'marketing_weight'      => [
                'label'  => __('Net weight'),
                'filled' => "coalesce({$table}.marketing_weight, 0) > 0",
            ],
            'gross_weight'          => [
                'label'  => __('Shipping weight'),
                'filled' => "coalesce({$table}.gross_weight, 0) > 0",
            ],
            'marketing_dimensions'  => [
                'label'  => __('Dimensions'),
                'filled' => "coalesce({$table}.marketing_dimensions::text, 'null') not in ('{}', '[]', 'null')",
            ],
            'marketing_ingredients' => [
                'label'  => __('Material / ingredients'),
                'filled' => "coalesce(trim({$table}.marketing_ingredients), '') <> ''",
            ],
            'barcode'               => [
                'label'  => __('Barcode'),
                'filled' => "(coalesce(trim({$table}.barcode), '') <> '' or exists (
                    select 1 from model_has_trade_units
                    join trade_units on trade_units.id = model_has_trade_units.trade_unit_id
                    where model_has_trade_units.model_type = '$modelType'
                      and model_has_trade_units.model_id = {$table}.id
                      and coalesce(trim(trade_units.barcode), '') <> ''
                ))",
            ],
            'origin_country'        => [
                'label'  => __('Origin country'),
                'filled' => "({$table}.origin_country_id is not null or coalesce(trim({$table}.country_of_origin), '') <> '')",
            ],
        ];
    }

    public function addSelects(EloquentBuilder|Builder|QueryBuilder $query): void
    {
        foreach ($this->items() as $key => $item) {
            $query->selectRaw("({$item['filled']}) as checklist_$key");
        }
    }

    public function whereIncomplete(EloquentBuilder|Builder $query, string $table = 'master_assets', string $modelType = 'MasterAsset'): void
    {
        $query->where(function ($query) use ($table, $modelType) {
            foreach ($this->items($table, $modelType) as $item) {
                $query->orWhereRaw("not ({$item['filled']})");
            }
        });
    }

    /**
     * One master product with its product checks, ready for handle().
     */
    public function checklistRow(MasterAsset $masterAsset): MasterAsset
    {
        $query = MasterAsset::query()->whereKey($masterAsset->id)->select('master_assets.id');
        $this->addSelects($query);

        /** @var MasterAsset */
        return $query->firstOrFail();
    }

    /**
     * @return array{complete: int, total: int, items: array<int, array{key: string, label: string, done: bool}>, missing: array<int, string>}|null
     */
    public function handle(Model $row): ?array
    {
        if (!array_key_exists('checklist_description', $row->getAttributes())) {
            return null;
        }

        $items = [];
        foreach ($this->items() as $key => $item) {
            $items[] = [
                'key'   => $key,
                'label' => $item['label'],
                'done'  => (bool)$row->getAttribute("checklist_$key"),
            ];
        }

        $missing = collect($items)->where('done', false)->pluck('label')->values()->all();

        return [
            'complete' => count($items) - count($missing),
            'total'    => count($items),
            'items'    => $items,
            'missing'  => $missing,
        ];
    }
}

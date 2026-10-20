<?php

/*
    * Author: Vika Aqordi
    * Created on: 2026-10-20 09:30
    * Github: https://github.com/aqordeon
    * Copyright: 2026
*/

namespace App\Actions\Goods\Packaging;

use App\Enums\Goods\Packaging\PackagingLevelEnum;
use App\Enums\Goods\Packaging\PackagingMaterialCategoryEnum;
use App\Enums\Goods\Packaging\PackagingPolymerEnum;
use App\Enums\SupplyChain\PackingSpec\PackingSpecRoleEnum;
use App\Models\Goods\PackagingComponent;
use App\Models\SysAdmin\Group;
use Illuminate\Console\Command;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * Keeps the packing materials agents pick from when they declare a packing spec, in English, Chinese and Indonesian,
 * each with the material it is reported as. Weights stay empty: agents declare them and goods in measures them.
 * Silica gel is in the list for the packers' instruction but is not reported. "Other" sends the spec to review.
 */
class SyncPackingDropdownComponents
{
    use AsAction;

    public string $commandSignature = 'packaging:sync-packing-dropdown';

    public const array ITEMS = [
        'bubble_wrap'      => ['Bubble wrap', '气泡膜', 'Bubble wrap', 'cushioning', 'plastic', 'ldpe'],
        'air_pillow'       => ['Air pillows', '充气袋', 'Bantal udara', 'void_fill', 'plastic', 'ldpe'],
        'epe_foam'         => ['EPE foam sheet / net', '珍珠棉', 'Busa EPE (lembar / jaring)', 'cushioning', 'plastic', null],
        'eps_foam'         => ['Polystyrene (EPS) block or chips', '泡沫块 / 泡沫粒 (EPS)', 'Styrofoam (EPS) blok / butiran', 'cushioning', 'plastic', 'eps'],
        'shrink_film'      => ['Shrink film (POF / PVC)', '热收缩膜', 'Plastik shrink', 'wrap', 'plastic', null],
        'stretch_film'     => ['Stretch film', '缠绕膜', 'Plastik stretch / wrapping', 'wrap', 'plastic', 'ldpe'],
        'pe_bag'           => ['Plastic bag (PE)', 'PE 胶袋', 'Kantong plastik (PE)', 'bag', 'plastic', 'ldpe'],
        'opp_bag'          => ['Clear OPP bag', 'OPP 透明袋', 'Kantong OPP bening', 'bag', 'plastic', 'pp'],
        'zip_bag'          => ['Zip-lock bag', '自封袋', 'Kantong zip-lock', 'bag', 'plastic', 'ldpe'],
        'tissue_paper'     => ['Tissue paper', '拷贝纸 / 薄纸', 'Kertas tisu', 'wrap', 'paper_cardboard', null],
        'kraft_paper'      => ['Kraft / wrapping paper', '牛皮纸 / 包装纸', 'Kertas kraft / pembungkus', 'wrap', 'paper_cardboard', null],
        'newspaper'        => ['Newspaper', '报纸', 'Kertas koran', 'void_fill', 'paper_cardboard', null],
        'shredded_paper'   => ['Shredded / crinkle paper', '碎纸 / 拉菲纸', 'Kertas cacah / kertas kerut', 'void_fill', 'paper_cardboard', null],
        'honeycomb_paper'  => ['Honeycomb paper', '蜂窝纸', 'Kertas honeycomb', 'cushioning', 'paper_cardboard', null],
        'cardboard_sheet'  => ['Cardboard sheet / divider', '纸板 / 隔板', 'Lembaran karton / sekat', 'divider', 'paper_cardboard', null],
        'pulp_tray'        => ['Moulded pulp tray', '纸浆托', 'Nampan pulp kertas', 'divider', 'paper_cardboard', null],
        'pack_box'         => ['Pack box (cardboard)', '内盒 (纸盒)', 'Kotak pack (karton)', 'pack_box', 'paper_cardboard', null],
        'master_carton'    => ['Master carton (corrugated)', '外箱 (瓦楞纸箱)', 'Karton master (bergelombang)', 'master_carton', 'paper_cardboard', null],
        'wood_wool'        => ['Wood wool / straw', '木丝 / 稻草', 'Serutan kayu / jerami', 'void_fill', 'wood', null],
        'wooden_crate'     => ['Wooden crate / frame', '木箱 / 木架', 'Peti / rangka kayu', 'master_carton', 'wood', null],
        'textile_bag'      => ['Cotton / jute bag', '棉布袋 / 麻袋', 'Kantong katun / goni', 'bag', 'textile', null],
        'strapping'        => ['Plastic strapping band', '打包带', 'Tali strapping plastik', 'strapping', 'plastic', 'pp'],
        'plastic_tape'     => ['Plastic packing tape', '封箱胶带', 'Lakban plastik', 'tape', 'plastic', 'pp'],
        'paper_tape'       => ['Paper tape', '牛皮纸胶带', 'Lakban kertas', 'tape', 'paper_cardboard', null],
        'label_insert'     => ['Label / insert card', '标签 / 说明卡', 'Label / kartu sisipan', 'label_insert', 'paper_cardboard', null],
        'silica_gel'       => ['Silica gel sachet', '干燥剂 (硅胶包)', 'Sachet silica gel', 'desiccant', 'other', null],
        'other'            => ['Other (describe it)', '其他 (请说明)', 'Lainnya (jelaskan)', 'wrap', 'other', null],
    ];

    public const array NOT_EPR_REPORTABLE = ['silica_gel'];

    public const string OTHER = 'other';

    public function handle(Group $group): int
    {
        $position = 0;
        foreach (self::ITEMS as $key => [$english, $chinese, $indonesian, $role, $material, $polymer]) {
            PackagingComponent::updateOrCreate(
                ['group_id' => $group->id, 'dropdown_key' => $key],
                [
                    'name'              => $english,
                    'is_dropdown_item'  => true,
                    'dropdown_labels'   => ['en' => $english, 'zh' => $chinese, 'id' => $indonesian],
                    'dropdown_position' => ++$position,
                    'default_role'      => PackingSpecRoleEnum::from($role),
                    'packaging_level'   => PackagingLevelEnum::SHIPMENT,
                    'material_category' => PackagingMaterialCategoryEnum::from($material),
                    'polymer'           => $polymer ? PackagingPolymerEnum::from($polymer) : null,
                    'is_epr_reportable' => !in_array($key, self::NOT_EPR_REPORTABLE),
                    'needs_review'      => $key === self::OTHER,
                ]
            );
        }

        return $position;
    }

    public function asCommand(Command $command): int
    {
        foreach (Group::all() as $group) {
            $command->info("$group->slug: {$this->handle($group)} packing materials");
        }

        return 0;
    }
}

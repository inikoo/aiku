<?php

/*
 * Author Louis Perez
 * Created on 15-10-2026-16h-55m
 * GitHub: https://github.com/louis-perez
 * Copyright 2026
*/

namespace App\Actions\Goods\ProductDevelopment;

use App\Enums\Goods\ProductDevelopment\ProductDevelopmentChecklistLevelEnum;
use App\Enums\Goods\ProductDevelopment\ProductDevelopmentMemberRoleEnum;
use App\Enums\Goods\ProductDevelopment\ProductDevelopmentTargetFieldEnum;
use App\Models\Goods\ProductDevelopmentChecklistTemplate;
use App\Models\SysAdmin\Group;
use Illuminate\Console\Command;
use Lorisleiva\Actions\Concerns\AsAction;

class SeedProductDevelopmentChecklistTemplates
{
    use AsAction;

    public const string DEFAULT_CATEGORY = 'default';

    public string $commandSignature = 'product_development:seed_checklist_templates';

    public function handle(Group $group): void
    {
        foreach ($this->starterSet() as $position => $template) {
            ProductDevelopmentChecklistTemplate::firstOrCreate(
                [
                    'group_id' => $group->id,
                    'category' => self::DEFAULT_CATEGORY,
                    'key'      => $template['key'],
                ],
                [
                    'role'         => $template['role'],
                    'level'        => $template['level'],
                    'label'        => $template['label'],
                    'required'     => $template['required'] ?? true,
                    'target_field' => $template['target_field'] ?? null,
                    'position'     => $position,
                ]
            );
        }
    }

    /**
     * @return array<int, array{key: string, role: string, level: string, label: string, required?: bool, target_field?: string}>
     */
    private function starterSet(): array
    {
        $family = ProductDevelopmentChecklistLevelEnum::FAMILY->value;
        $sku    = ProductDevelopmentChecklistLevelEnum::SKU->value;

        $fieldItem = fn (ProductDevelopmentMemberRoleEnum $role, ProductDevelopmentTargetFieldEnum $field, bool $required = true) => [
            'key'          => $field->value,
            'role'         => $role->value,
            'level'        => $sku,
            'label'        => ProductDevelopmentTargetFieldEnum::labels()[$field->value],
            'required'     => $required,
            'target_field' => $field->value,
        ];

        return [
            ['key' => 'brief_target_customer', 'role' => ProductDevelopmentMemberRoleEnum::BUYER->value, 'level' => $family, 'label' => __('Target customer and price point')],
            ['key' => 'brief_references', 'role' => ProductDevelopmentMemberRoleEnum::BUYER->value, 'level' => $family, 'label' => __('Reference images and mood')],
            $fieldItem(ProductDevelopmentMemberRoleEnum::AGENT, ProductDevelopmentTargetFieldEnum::TRADE_UNIT_NET_WEIGHT),
            $fieldItem(ProductDevelopmentMemberRoleEnum::AGENT, ProductDevelopmentTargetFieldEnum::TRADE_UNIT_GROSS_WEIGHT),
            $fieldItem(ProductDevelopmentMemberRoleEnum::AGENT, ProductDevelopmentTargetFieldEnum::TRADE_UNIT_MARKETING_DIMENSIONS),
            $fieldItem(ProductDevelopmentMemberRoleEnum::AGENT, ProductDevelopmentTargetFieldEnum::TRADE_UNIT_ORIGIN_COUNTRY),
            $fieldItem(ProductDevelopmentMemberRoleEnum::AGENT, ProductDevelopmentTargetFieldEnum::SUPPLIER_PRODUCT_UNITS_PER_PACK),
            $fieldItem(ProductDevelopmentMemberRoleEnum::AGENT, ProductDevelopmentTargetFieldEnum::SUPPLIER_PRODUCT_UNITS_PER_CARTON),
            $fieldItem(ProductDevelopmentMemberRoleEnum::AGENT, ProductDevelopmentTargetFieldEnum::SUPPLIER_PRODUCT_CBM),
            $fieldItem(ProductDevelopmentMemberRoleEnum::AGENT, ProductDevelopmentTargetFieldEnum::SUPPLIER_PRODUCT_CARTON_WEIGHT),
            $fieldItem(ProductDevelopmentMemberRoleEnum::PURCHASING, ProductDevelopmentTargetFieldEnum::SUPPLIER_PRODUCT_COST),
            $fieldItem(ProductDevelopmentMemberRoleEnum::PURCHASING, ProductDevelopmentTargetFieldEnum::SUPPLIER_PRODUCT_LEAD_TIME),
            ['key' => 'charge_label_print', 'role' => ProductDevelopmentMemberRoleEnum::PURCHASING->value, 'level' => $family, 'label' => __('Agreed label print charge'), 'required' => false],
            $fieldItem(ProductDevelopmentMemberRoleEnum::COMPLIANCE, ProductDevelopmentTargetFieldEnum::TRADE_UNIT_TARIFF_CODE),
            $fieldItem(ProductDevelopmentMemberRoleEnum::COMPLIANCE, ProductDevelopmentTargetFieldEnum::TRADE_UNIT_MARKETING_INGREDIENTS),
            $fieldItem(ProductDevelopmentMemberRoleEnum::COMPLIANCE, ProductDevelopmentTargetFieldEnum::TRADE_UNIT_GPSR_MANUFACTURER),
            $fieldItem(ProductDevelopmentMemberRoleEnum::COMPLIANCE, ProductDevelopmentTargetFieldEnum::TRADE_UNIT_GPSR_EU_RESPONSIBLE),
            $fieldItem(ProductDevelopmentMemberRoleEnum::COMPLIANCE, ProductDevelopmentTargetFieldEnum::TRADE_UNIT_GPSR_WARNINGS),
            $fieldItem(ProductDevelopmentMemberRoleEnum::COMPLIANCE, ProductDevelopmentTargetFieldEnum::TRADE_UNIT_UFI_NUMBER, false),
            $fieldItem(ProductDevelopmentMemberRoleEnum::COMPLIANCE, ProductDevelopmentTargetFieldEnum::TRADE_UNIT_UN_NUMBER, false),
        ];
    }

    public function asCommand(Command $command): int
    {
        foreach (Group::all() as $group) {
            $this->handle($group);
            $command->info("Checklist templates seeded for {$group->slug}");
        }

        return 0;
    }
}

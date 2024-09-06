<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 06 Jul 2023 12:33:47 Malaysia Time, plane Bali -> KL
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Stubs\Migrations;

use App\Enums\Catalogue\Product\ProductStateEnum;
use App\Enums\Fulfilment\Rental\RentalStateEnum;
use App\Enums\Catalogue\Asset\AssetStateEnum;
use App\Enums\Catalogue\ProductCategory\ProductCategoryStateEnum;
use App\Enums\Catalogue\Service\ServiceStateEnum;
use App\Enums\Catalogue\Shop\ShopStateEnum;
use App\Enums\Catalogue\Shop\ShopTypeEnum;
use Illuminate\Database\Schema\Blueprint;

trait HasCatalogueStats
{
    public function shopsStats(Blueprint $table): Blueprint
    {
        $table->unsignedSmallInteger('number_shops')->default(0);
        foreach (ShopStateEnum::cases() as $shopState) {
            $table->unsignedSmallInteger('number_shops_state_'.$shopState->snake())->default(0);
        }
        foreach (ShopTypeEnum::cases() as $shopType) {
            $table->unsignedSmallInteger('number_shops_type_' . $shopType->snake())->default(0);
        }
        return $table;
    }


    public function productVariantFields(Blueprint $table): Blueprint
    {
        $table->unsignedInteger('number_product_variants')->default(0);
        return $table;
    }

    public function catalogueStats(Blueprint $table): Blueprint
    {
        $table->unsignedInteger('number_departments')->default(0);
        $table->unsignedInteger('number_current_departments')->default(0);

        foreach (ProductCategoryStateEnum::cases() as $departmentState) {
            $table->unsignedInteger('number_departments_state_'.$departmentState->snake())->default(0);
        }

        $table->unsignedInteger('number_collection_categories')->default(0);
        $table->unsignedInteger('number_collections')->default(0);
        $table= $this->catalogueFamilyStats($table);
        $table= $this->assetStats($table);
        $table= $this->catalogueProductsStats($table);

        return $this->productVariantFields($table);
    }

    public function catalogueFamilyStats(Blueprint $table): Blueprint
    {
        $table->unsignedInteger('number_sub_departments')->default(0);
        $table->unsignedSmallInteger('number_current_sub_departments')->default(0)->comment('state: active+discontinuing');
        foreach (ProductCategoryStateEnum::cases() as $familyState) {
            $table->unsignedInteger('number_sub_departments_state_'.$familyState->snake())->default(0);
        }

        $table->unsignedInteger('number_families')->default(0);
        $table->unsignedSmallInteger('number_current_families')->default(0)->comment('state: active+discontinuing');
        foreach (ProductCategoryStateEnum::cases() as $familyState) {
            $table->unsignedInteger('number_families_state_'.$familyState->snake())->default(0);
        }
        $table->unsignedInteger('number_orphan_families')->default(0);
        return $table;

    }

    public function assetStats(Blueprint $table): Blueprint
    {

        $table->unsignedInteger('number_assets')->default(0);
        $table->unsignedInteger('number_current_assets')->default(0)->comment('state: active+discontinuing');
        $table->unsignedInteger('number_historic_assets')->default(0);

        return $this->assetStatsBis($table);
    }

    public function assetStatsBis(Blueprint $table): Blueprint
    {


        foreach (AssetStateEnum::cases() as $case) {
            $table->unsignedInteger('number_assets_state_'.$case->snake())->default(0);
        }


        $table->unsignedInteger('number_assets_type_product')->default(0);
        $table->unsignedInteger('number_assets_type_service')->default(0);
        $table->unsignedInteger('number_assets_type_subscription')->default(0);
        $table->unsignedInteger('number_assets_type_rental')->default(0);
        $table->unsignedInteger('number_assets_type_charge')->default(0);
        $table->unsignedInteger('number_assets_type_shipping_zone')->default(0);

        return $table;
    }

    public function catalogueProductsStats(Blueprint $table): Blueprint
    {

        $table->unsignedInteger('number_products')->default(0);
        $table->unsignedInteger('number_current_products')->default(0)->comment('state: active+discontinuing');

        foreach (ProductStateEnum::cases() as $productState) {
            $table->unsignedInteger('number_products_state_'.$productState->snake())->default(0);
        }

        $table->unsignedInteger('number_rentals')->default(0);

        foreach (RentalStateEnum::cases() as $case) {
            $table->unsignedInteger('number_rentals_state_'.$case->snake())->default(0);
        }

        $table->unsignedInteger('number_services')->default(0);

        foreach (ServiceStateEnum::cases() as $case) {
            $table->unsignedInteger('number_services_state_'.$case->snake())->default(0);
        }

        $table->unsignedInteger('number_subscriptions')->default(0);

        foreach (ServiceStateEnum::cases() as $case) {
            $table->unsignedInteger('number_subscriptions_state_'.$case->snake())->default(0);
        }

        return $table;
    }
}

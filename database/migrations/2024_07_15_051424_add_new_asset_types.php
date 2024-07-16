<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 15 Jul 2024 13:14:38 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

use App\Enums\Catalogue\Charge\ChargeStateEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('shop_stats', function (Blueprint $table) {
            $table->unsignedInteger('number_assets_type_charge')->default(0);
            $table->unsignedInteger('number_assets_type_shipping')->default(0);
            $table->unsignedInteger('number_assets_type_insurance')->default(0);
            $table->unsignedInteger('number_assets_type_adjustment')->default(0);

            $table->unsignedInteger('number_charges')->default(0);
            foreach (ChargeStateEnum::cases() as $case) {
                $table->unsignedInteger('number_charges_state_'.$case->snake())->default(0);
            }

            $table->unsignedInteger('number_shippings')->default(0);
            foreach (ChargeStateEnum::cases() as $case) {
                $table->unsignedInteger('number_shippings_state_'.$case->snake())->default(0);
            }

            $table->unsignedInteger('number_insurances')->default(0);
            foreach (ChargeStateEnum::cases() as $case) {
                $table->unsignedInteger('number_insurances_state_'.$case->snake())->default(0);
            }



        });
        Schema::table('organisation_catalogue_stats', function (Blueprint $table) {
            $table->unsignedInteger('number_assets_type_charge')->default(0);
            $table->unsignedInteger('number_assets_type_shipping')->default(0);
            $table->unsignedInteger('number_assets_type_insurance')->default(0);
            $table->unsignedInteger('number_assets_type_adjustment')->default(0);

            $table->unsignedInteger('number_charges')->default(0);
            foreach (ChargeStateEnum::cases() as $case) {
                $table->unsignedInteger('number_charges_state_'.$case->snake())->default(0);
            }

            $table->unsignedInteger('number_shippings')->default(0);
            foreach (ChargeStateEnum::cases() as $case) {
                $table->unsignedInteger('number_shippings_state_'.$case->snake())->default(0);
            }

            $table->unsignedInteger('number_insurances')->default(0);
            foreach (ChargeStateEnum::cases() as $case) {
                $table->unsignedInteger('number_insurances_state_'.$case->snake())->default(0);
            }

        });
        Schema::table('group_catalogue_stats', function (Blueprint $table) {
            $table->unsignedInteger('number_assets_type_charge')->default(0);
            $table->unsignedInteger('number_assets_type_shipping')->default(0);
            $table->unsignedInteger('number_assets_type_insurance')->default(0);
            $table->unsignedInteger('number_assets_type_adjustment')->default(0);

            $table->unsignedInteger('number_charges')->default(0);
            foreach (ChargeStateEnum::cases() as $case) {
                $table->unsignedInteger('number_charges_state_'.$case->snake())->default(0);
            }

            $table->unsignedInteger('number_shippings')->default(0);
            foreach (ChargeStateEnum::cases() as $case) {
                $table->unsignedInteger('number_shippings_state_'.$case->snake())->default(0);
            }

            $table->unsignedInteger('number_insurances')->default(0);
            foreach (ChargeStateEnum::cases() as $case) {
                $table->unsignedInteger('number_insurances_state_'.$case->snake())->default(0);
            }

        });
        Schema::table('asset_stats', function (Blueprint $table) {
            $table->unsignedInteger('number_assets_type_charge')->default(0);
            $table->unsignedInteger('number_assets_type_shipping')->default(0);
            $table->unsignedInteger('number_assets_type_insurance')->default(0);
            $table->unsignedInteger('number_assets_type_adjustment')->default(0);

        });
    }


    public function down(): void
    {
        Schema::table('asset_stats', function (Blueprint $table) {
            $table->dropColumn(['number_assets_type_charges', 'number_assets_type_shipping', 'number_assets_type_insurance','number_assets_type_adjustment']);
        });
        Schema::table('group_catalogue_stats', function (Blueprint $table) {
            $table->dropColumn(['number_assets_type_charges', 'number_assets_type_shipping', 'number_assets_type_insurance','number_assets_type_adjustment']);
        });
        Schema::table('organisation_catalogue_stats', function (Blueprint $table) {
            $table->dropColumn(['number_assets_type_charges', 'number_assets_type_shipping', 'number_assets_type_insurance','number_assets_type_adjustment']);
        });
        Schema::table('shop_stats', function (Blueprint $table) {
            $table->dropColumn(['number_assets_type_charges', 'number_assets_type_shipping', 'number_assets_type_insurance','number_assets_type_adjustment']);
        });
    }
};

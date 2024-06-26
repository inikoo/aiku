<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 26 Jun 2024 15:04:11 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

use App\Enums\Catalogue\Product\ProductStateEnum;
use App\Enums\CRM\Customer\CustomerStateEnum;
use App\Enums\Ordering\Transaction\TransactionStateEnum;
use App\Enums\Ordering\Transaction\TransactionStatusEnum;
use App\Enums\Ordering\Transaction\TransactionTypeEnum;
use App\Stubs\Migrations\HasCatalogueStats;
use App\Stubs\Migrations\HasSalesIntervals;
use App\Stubs\Migrations\HasSalesStats;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    use HasSalesIntervals;
    use HasCatalogueStats;
    use HasSalesStats;

    public function up(): void
    {
        Schema::create('platform_stats', function (Blueprint $table) {
            $table->smallIncrements('id');
            $table->unsignedSmallInteger('platform_id')->index();
            $table->foreign('platform_id')->references('id')->on('platforms')->onUpdate('cascade')->onDelete('cascade');

            $table->unsignedInteger('number_customers')->default(0);

            foreach (CustomerStateEnum::cases() as $customerState) {
                $table->unsignedInteger("number_customers_state_{$customerState->snake()}")->default(0);
            }

            $table=$this->salesStatsFields($table);

            $table = $this->productVariantFields($table);

            $table->unsignedInteger('number_historic_assets')->default(0);
            foreach (ProductStateEnum::cases() as $case) {
                $table->unsignedInteger('number_products_state_'.$case->snake())->default(0);
            }

            $table->unsignedSmallInteger('number_transactions_at_creation')->default(0);
            $table->unsignedSmallInteger('number_add_up_transactions')->default(0);
            $table->unsignedSmallInteger('number_cut_off_transactions')->default(0);

            $table->unsignedSmallInteger('number_transactions')->default(0)->comment('transactions including cancelled');
            $table->unsignedSmallInteger('number_current_transactions')->default(0)->comment('transactions excluding cancelled');

            foreach (TransactionStateEnum::cases() as $case) {
                $table->unsignedInteger('number_transactions_state_'.$case->snake())->default(0);
            }

            foreach (TransactionStatusEnum::cases() as $case) {
                $table->unsignedInteger('number_transactions_status_'.$case->snake())->default(0);
            }

            foreach (TransactionTypeEnum::cases() as $case) {
                $table->unsignedInteger('number_transactions_type_'.$case->snake())->default(0);
            }

            $table->timestampsTz();
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('platform_stats');
    }
};

<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Wed, 21 Dec 2022 15:19:17 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

use App\Enums\Ordering\Transaction\TransactionStateEnum;
use App\Enums\Ordering\Transaction\TransactionStatusEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('order_stats', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('order_id')->index();
            $table->foreign('order_id')->references('id')->on('orders');

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

            $table->timestampsTz();
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('order_stats');
    }
};

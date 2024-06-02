<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 01 Jun 2024 23:26:48 Central European Summer Time, Mijas Costa, Spain
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

use App\Enums\Catalogue\Subscription\SubscriptionStateEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('subscription_stats', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('subscription_id')->index();
            $table->foreign('subscription_id')->references('id')->on('subscriptions');

            $table->unsignedInteger('number_historic_assets')->default(0);
            foreach (SubscriptionStateEnum::cases() as $case) {
                $table->unsignedInteger('number_subscriptions_state_'.$case->snake())->default(0);
            }

            $table->timestampsTz();
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('subscription_stats');
    }
};

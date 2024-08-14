<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 14 Aug 2024 11:07:18 Central Indonesia Time, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('shopify_plans', function (Blueprint $table) {
            $table->increments('id');

            // The type of plan, either PlanType::RECURRING (0) or PlanType::ONETIME (1)
            $table->string('type');

            // Name of the plan
            $table->string('name');

            // Price of the plan
            $table->decimal('price');

            // Store the amount of the charge, this helps if you are experimenting with pricing
            $table->decimal('capped_amount')->nullable();

            // Terms for the usage charges
            $table->string('terms')->nullable();

            // Nullable in case of 0 trial days
            $table->integer('trial_days')->nullable();

            // Is a test plan or not
            $table->boolean('test')->default(false);

            // On-install
            $table->boolean('on_install')->default(false);

            // Provides created_at && updated_at columns
            $table->timestamps();
        });
    }


    public function down(): void
    {
        Schema::drop('shopify_plans');
    }
};

<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 14 Aug 2024 11:07:40 Central Indonesia Time, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Schema;
use Osiset\ShopifyApp\Util;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create(Util::getShopifyConfig('table_names.charges', 'charges'), function (Blueprint $table) {
            $table->increments('id');

            // Filled in when the charge is created, provided by shopify, unique makes it indexed
            $table->bigInteger('charge_id');

            // Test mode or real
            $table->boolean('test')->default(false);

            $table->string('status')->nullable();

            // Name of the charge (for recurring or one time charges)
            $table->string('name')->nullable();

            // Terms for the usage charges
            $table->string('terms')->nullable();

            // Integer value representing a recurring, one time, usage, or application_credit.
            // This also allows us to store usage based charges not just subscription or one time charges.
            // We will be able to do things like create a charge history for a shop if they have multiple charges.
            // For instance, usage based or an app that has multiple purchases.
            $table->string('type');

            // Store the amount of the charge, this helps if you are experimenting with pricing
            $table->decimal('price');

            // Store the amount of the charge, this helps if you are experimenting with pricing
            $table->decimal('capped_amount')->nullable();

            // Nullable in case of 0 trial days
            $table->integer('trial_days')->nullable();

            // The recurring application charge must be accepted or the returned value is null
            $table->timestamp('billing_on')->nullable();

            // When activation happened
            $table->timestamp('activated_on')->nullable();

            // Date the trial period ends
            $table->timestamp('trial_ends_on')->nullable();

            // Not supported on Shopify initial billing screen, but good for future use
            $table->timestamp('cancelled_on')->nullable();

            // Expires on
            $table->timestamp('expires_on')->nullable();

            // Plan ID for the charge
            $table->integer('plan_id')->unsigned()->nullable();

            // Description support
            $table->string('description')->nullable();

            // Linking to charge_id
            $table->bigInteger('reference_charge')->nullable();

            // Provides created_at && updated_at columns
            $table->timestamps();

            // Allows for soft deleting
            $table->softDeletes();

            if ($this->getLaravelVersion() < 5.8) {
                $table->integer(Util::getShopsTableForeignKey())->unsigned();
            } else {
                $table->bigInteger(Util::getShopsTableForeignKey())->unsigned();
            }

            // Linking
            $table->foreign(Util::getShopsTableForeignKey())->references('id')->on(Util::getShopsTable())->onDelete('cascade');
            $table->foreign('plan_id')->references('id')->on(Util::getShopifyConfig('table_names.plans', 'plans'));
        });
    }


    public function down(): void
    {
        Schema::drop(Util::getShopifyConfig('table_names.charges', 'charges'));
    }


    private function getLaravelVersion(): float
    {
        $version = Application::VERSION;

        return (float) substr($version, 0, strrpos($version, '.'));
    }
};

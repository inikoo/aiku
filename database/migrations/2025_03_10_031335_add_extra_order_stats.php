<?php

use App\Enums\Ordering\Order\OrderStateEnum;
use App\Enums\Ordering\Order\OrderStatusEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('group_ordering_stats', function (Blueprint $table) {
            $this->orderingFields($table);
        });

        Schema::table('organisation_ordering_stats', function (Blueprint $table) {
            $this->orderingFields($table);
        });

        Schema::table('shop_ordering_stats', function (Blueprint $table) {
            $this->orderingFields($table);
        });
    }


    public function down(): void
    {
        Schema::table('group_ordering_stats', function (Blueprint $table) {
            $this->rollBackOrderingFields($table);
        });

        Schema::table('organisation_ordering_stats', function (Blueprint $table) {
            $this->rollBackOrderingFields($table);
        });

        Schema::table('shop_ordering_stats', function (Blueprint $table) {
            $this->rollBackOrderingFields($table);
        });
    }


    public function orderingFields(Blueprint $table): Blueprint
    {

        $table->unsignedInteger('number_orders_stat_submitted_paid')->default(0);
        $table->unsignedInteger('number_orders_stat_submitted_unpaid')->default(0);


        foreach (OrderStateEnum::cases() as $case) {
            $table->decimal('orders_net_org_amount_state_'.$case->snake(), 16)->nullable();
            $table->decimal('orders_net_grp_amount_state_'.$case->snake(), 16)->nullable();
            $table->decimal('orders_net_amount_state_'.$case->snake(), 16)->nullable();
        }

        $table->unsignedInteger('number_orders_state_dispatched_today')->default(0);
        $table->decimal('orders_net_org_amount_state_dispatched_today', 16)->nullable();
        $table->decimal('orders_net_grp_amount_state_dispatched_today', 16)->nullable();
        $table->decimal('orders_net_amount_state_dispatched_today', 16)->nullable();

        foreach (OrderStatusEnum::cases() as $case) {
            $table->decimal('orders_net_org_amount_status_'.$case->snake(), 16)->nullable();
            $table->decimal('orders_net_grp_amount_status_'.$case->snake(), 16)->nullable();
            $table->decimal('orders_net_amount_status_'.$case->snake(), 16)->nullable();
        }

        return $table;
    }

    public function rollBackOrderingFields(Blueprint $table): Blueprint
    {
        $table->dropColumn('number_orders_stat_submitted_paid');
        $table->dropColumn('number_orders_stat_submitted_unpaid');

        foreach (OrderStateEnum::cases() as $case) {
            $table->dropColumn('orders_net_org_amount_state_'.$case->snake());
            $table->dropColumn('orders_net_grp_amount_state_'.$case->snake());
            $table->dropColumn('orders_net_amount_state_'.$case->snake());
        }

        $table->dropColumn('number_orders_state_dispatched_today');
        $table->dropColumn('orders_net_org_amount_state_dispatched_today');
        $table->dropColumn('orders_net_grp_amount_state_dispatched_today');
        $table->dropColumn('orders_net_amount_state_dispatched_today');

        foreach (OrderStatusEnum::cases() as $case) {
            $table->dropColumn('orders_net_org_amount_status_'.$case->snake());
            $table->dropColumn('orders_net_grp_amount_status_'.$case->snake());
            $table->dropColumn('orders_net_amount_status_'.$case->snake());
        }

        return $table;
    }
};

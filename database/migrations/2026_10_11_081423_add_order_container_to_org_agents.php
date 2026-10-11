<?php

use App\Enums\Procurement\OrgAgent\OrgAgentOrderContainerEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('org_agents', function (Blueprint $table) {
            $table->string('order_container')->default(OrgAgentOrderContainerEnum::FORTY_FOOT->value)->comment('container an order to this agent is measured against');
        });
    }

    public function down(): void
    {
        Schema::table('org_agents', function (Blueprint $table) {
            $table->dropColumn('order_container');
        });
    }
};

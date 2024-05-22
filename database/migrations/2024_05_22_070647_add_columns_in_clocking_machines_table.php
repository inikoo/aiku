<?php

use App\Enums\HumanResources\ClockingMachine\ClockingMachineStatusEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('clocking_machines', function (Blueprint $table) {
            $table->after('type', function ($table) {
                $table->string('status')->default(ClockingMachineStatusEnum::DISCONNECTED->value);
                $table->string('device_name')->nullable();
                $table->string('device_uuid')->index()->unique()->nullable();
                $table->string('qr_code')->nullable()->index()->unique();
            });
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('clocking_machines', function (Blueprint $table) {
            //
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserAuthLogsTable extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create(
            'user_auth_logs', function (Blueprint $table) {
            $table->timestampTz('time', 0);
            $table->string('handle')->index();

            $table->unsignedMediumInteger('user_id')->nullable()->index();
            $table->foreign('user_id')->references('id')->on('users');

            $table->string('ip', 15);
            $table->enum(
                'action', [
                            'login',
                            'logout',
                            'loginFail',
                            'logoutFail'
                        ]
            );
        }
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('user_auth_logs');
    }
}

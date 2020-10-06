<?php
/*
 * Author: Raul A Perusquía-Flores (raul@aiku.io)
 * Created: Sun, 16 Aug 2020 00:17:37 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2020. Aiku.io
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AuthStaff extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('job_positions', function (Blueprint $table) {
            $table->smallIncrements('id');
            $table->string('slug')->unique();
            $table->string('name');
            $table->json('data')->nullable();
            $table->timestampsTz();
        });

        Schema::create(
            'users', function (Blueprint $table) {
            $table->mediumIncrements('id');
            $table->unsignedSmallInteger('tenant_id');
            $table->boolean('status')->default(true);

            $table->string('handle',1000)->unique();
            $table->string('password');
            $table->string('pin');

            $table->string('userable_type',64);
            $table->unsignedMediumInteger('userable_id');

            $table->json('settings');
            $table->json('data');
            $table->json('confidential')->nullable();

            $table->timestampTZ('last_login_at')->nullable();
            $table->timestampTZ('last_login_fail_at')->nullable();

            $table->timestampsTz();
            $table->unsignedMediumInteger('legacy_id')->nullable();
            $table->index(
                [
                    'tenant_id',
                    'handle'
                ]
            );
            $table->index(
                [
                    'userable_type',
                    'userable_id'
                ]
            );
            $table->index('status');
        }
        );

        Schema::create('employees', function (Blueprint $table) {
            $table->smallIncrements('id');
            $table->unsignedSmallInteger('tenant_id');
            $table->unsignedSmallInteger('job_position_id')->nullable()->index();
            $table->foreign('job_position_id')->references('id')->on('job_positions');

            $table->unsignedSmallInteger('user_id')->nullable()->index();
            $table->foreign('user_id')->references('id')->on('users');

            $table->enum('status',['Working','NotWorking'])->default('Working');
            $table->string('slug')->nullable()->unique();
            $table->string('name');
            $table->json('settings');
            $table->json('data');
            $table->timestampsTz();
            $table->unsignedSmallInteger('legacy_id')->nullable();
            $table->index('status');

        });

        Schema::create('guests', function (Blueprint $table) {
            $table->id();

            $table->unsignedSmallInteger('user_id')->nullable()->index();
            $table->foreign('user_id')->references('id')->on('users');

            $table->boolean('status')->default(true)->index();
            $table->string('slug',1000)->unique();
            $table->string('name',500);
            $table->string('description',1000);
            $table->unsignedSmallInteger('legacy_id')->nullable();
            $table->unsignedSmallInteger('tenant_id');
            $table->json('data');
            $table->json('settings');
            $table->timestampsTz();

        });

        Schema::create('clocking_nfc_tags', function (Blueprint $table) {
            $table->smallIncrements('id');
            $table->string('uuid')->index();
            $table->unsignedSmallInteger('employee_id')->nullable()->index();
            $table->foreign('employee_id')->references('id')->on('employees');
            $table->timestampsTz();
        });

        Schema::create('timesheets', function (Blueprint $table) {
            $table->mediumIncrements('id');
            $table->unsignedSmallInteger('employee_id');
            $table->foreign('employee_id')->references('id')->on('employees');
            $table->unsignedMediumInteger('date_id');
            $table->foreign('date_id')->references('id')->on('dates');

            $table->unsignedSmallInteger('records')->default(0);
            $table->float('clocked_hours',4,2)->default(0);

            $table->date('date');
            $table->enum('status',['Working','Holiday','SickDay'])->default('Working');
            $table->json('data');
            $table->timestampsTz();
            $table->unsignedMediumInteger('legacy_id')->nullable();
            $table->index(['employee_id', 'date']);
            $table->index('status');
        });

        Schema::create('timesheet_records', function (Blueprint $table) {
            $table->id();
            $table->unsignedMediumInteger('timesheet_id');
            $table->foreign('timesheet_id')->references('id')->on('timesheets');
            $table->unsignedMediumInteger('clocking_nfc_tag_id')->nullable()->index();
            $table->foreign('clocking_nfc_tag_id')->references('id')->on('clocking_nfc_tags');
            $table->dateTimeTz('date')->index();
            $table->string('state');
            $table->json('data');
            $table->unsignedMediumInteger('legacy_id')->nullable();
            $table->timestampsTz();
        });

        Schema::create(
            'clocking_machines', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name')->index();
            $table->string('state')->index()->default('inProcess');
            $table->json('settings');
            $table->json('data');
            $table->timestampsTz();
        });
        Schema::create(
            'job_position_role', function (Blueprint $table) {
            $table->unsignedSmallInteger('job_position_id');
            $table->foreign('job_position_id')->references('id')->on('job_positions');

            $table->unsignedBigInteger('role_id');
            $table->foreign('role_id')->references('id')->on('roles');

            $table->primary(
                [
                    'job_position_id',
                    'role_id'
                ]
            );
        });

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
        });

        Schema::create('admins', function (Blueprint $table) {
            $table->mediumIncrements('id');
            $table->unsignedSmallInteger('tenant_id');

            $table->unsignedSmallInteger('user_id')->nullable()->index();
            $table->foreign('user_id')->references('id')->on('users');

            $table->string('slug',1000)->unique();
            $table->string('name');
            $table->json('settings');
            $table->json('data');
            $table->timestampsTz();
        });

        Schema::create('devices', function (Blueprint $table) {
            $table->mediumIncrements('id');
            $table->unsignedSmallInteger('tenant_id');
            $table->string('app',255)->index();

            $table->string('uid',255)->index();
            $table->string('tag',1000)->index();


            $table->unsignedMediumInteger('user_id')->nullable()->index();
            $table->foreign('user_id')->references('id')->on('users');

            $table->unsignedMediumInteger('personal_access_token_id')->nullable()->index();
            $table->foreign('personal_access_token_id')->references('id')->on('personal_access_tokens');


            $table->timestampsTz();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('devices');
        Schema::dropIfExists('admins');
        Schema::dropIfExists('user_auth_logs');
        Schema::dropIfExists('job_position_role');
        Schema::dropIfExists('clocking_machines');
        Schema::dropIfExists('timesheet_records');
        Schema::dropIfExists('timesheets');
        Schema::dropIfExists('clocking_nfc_tags');
        Schema::dropIfExists('guests');
        Schema::dropIfExists('employees');
        Schema::dropIfExists('users');
        Schema::dropIfExists('job_positions');








    }
}

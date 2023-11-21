<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->bigIncrements('user_id');
            $table->string('user_sys_id', 255)->unique();
            $table->string('user_email', 255)->unique();
            $table->string('user_phone', 255)->unique();
            $table->string('passcode');
            $table->datetime('passcode_set_time')->nullable();
            $table->string('user_fcm_token_android', 255)->default("");
            $table->string('user_fcm_token_web', 255)->default("");
            $table->string('user_fcm_token_ios', 255)->default("");
            $table->string('user_android_app_version_code', 255)->default("");
            $table->string('user_ios_app_version_code', 255)->default("");
            $table->boolean('user_flagged')->default(false);
            $table->text('user_flagged_reason')->nullable();
            $table->timestamps();
        });
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
};

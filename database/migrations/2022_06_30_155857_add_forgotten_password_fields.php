<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForgottenPasswordFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('forgotten_password_created_at')->nullable();
            $table->string('forgotten_password_code', 6)->nullable();
            $table->tinyInteger('forgotten_password_code_confirmed')->default(0);
        });
        Schema::table('users', function (Blueprint $table) {
            $table->unique(["forgotten_password_code"], 'uinx_fp_code');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}

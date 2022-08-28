<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateRolesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 255)->default('');
        });
        Schema::table('users', function(Blueprint $table) {
            $table->integer('id_role')->unsigned()->nullable()->default(null);
            $table->tinyInteger('active')->default(1);
            $table->foreign('id_role')->references('id')->on('roles');//->nullOnDelete()->cascadeOnUpdate();
        });
        DB::table('roles')->insert([
            ['name' => 'seller'],
            ['name' => 'client']
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('roles');
    }
}

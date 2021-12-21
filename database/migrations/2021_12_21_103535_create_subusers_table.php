<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSubusersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('subusers', function (Blueprint $table) {
            $table->bigInteger('user');
           // $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->bigInteger('subuser');
           // $table->foreign('subuser')->references('id')->on('users')->onDelete('cascade');
            $table->string('link');
            $table->primary(['user', 'subuser']);
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
        Schema::dropIfExists('subusers');
    }
}

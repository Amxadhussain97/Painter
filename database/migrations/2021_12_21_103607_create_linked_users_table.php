<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLinkedUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('linked_users', function (Blueprint $table) {
            $table->bigInteger('user');
            //$table->foreign('user')->references('id')->on('users')->onDelete('cascade');
            $table->bigInteger('linkeduser');
          // $table->foreign('linkeduser')->references('id')->on('users')->onDelete('cascade');
            $table->string('link');
            $table->primary(['user', 'linkeduser']);
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
        Schema::dropIfExists('linked_users');
    }
}

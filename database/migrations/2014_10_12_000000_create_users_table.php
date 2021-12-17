<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('email')->unique()->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password')->nullable();
            $table->string('gender')->nullable();
            $table->string('imagePath')->nullable();
            $table->string('birthDate')->nullable();
            $table->string('area')->nullable();
            $table->string('bankName')->nullable();
            $table->string('rocket')->nullable();
            $table->string('bkash')->nullable();
            $table->string('phone')->nullable();
            $table->string('nogod')->nullable();
            $table->unsignedBigInteger('subdistrict_id')->nullable();
            $table->foreign('subdistrict_id')->references('id')->on('subdistricts')->onDelete('cascade');
            $table->string('role')->nullable();
            $table->rememberToken();
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
}

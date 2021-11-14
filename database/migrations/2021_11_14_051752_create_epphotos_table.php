<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEpphotosTable extends PhotoMigration
{
    public function up()
    {
        parent::up2('epphotos');
        Schema::table('epphotos', function (Blueprint $table) {
            $table->unsignedBigInteger('eptool_id');
            $table->foreign('eptool_id')->references('id')->on('eptools')->onDelete('cascade');

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('epphotos');
    }
}

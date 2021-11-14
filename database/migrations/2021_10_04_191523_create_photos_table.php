<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


class PhotoMigration extends Migration
{

    private $tableName;
    public function up2($tableName)
    {
        $this->tableName = $tableName;
        Schema::create($tableName, function (Blueprint $table) {
            $table->id();
            $table->string('image_id');
        });
    }
}

class CreatePhotosTable extends PhotoMigration
{
    public function up()
    {
        parent::up2('photos');
        Schema::table('photos', function (Blueprint $table) {
            $table->unsignedBigInteger('gallery_id');
            $table->foreign('gallery_id')->references('id')->on('galleries')->onDelete('cascade');
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
        Schema::dropIfExists('photos');
    }
}

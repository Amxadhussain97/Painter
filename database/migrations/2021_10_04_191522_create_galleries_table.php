<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


class GalleryMigration extends Migration {

    private $tableName;
    public function up2($tableName) {
        $this->tableName = $tableName;
      Schema::create($tableName, function (Blueprint $table) {
        $table->id();
        $table->string('name');

      });
    }
  }

class CreateGalleriesTable extends GalleryMigration {
    public function up() {
        parent::up2('galleries');
        Schema::table('galleries', function (Blueprint $table) {
          $table->unsignedBigInteger('user_id');
          $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

          $table->timestamps();
        });
    }

}


// class CreateEptoolgalleriesTable extends BaseMigration {
//     public function up() {
//         parent::up2('eptoolgalleries');
//         Schema::table('eptoolgalleries', function (Blueprint $table) {

//           $table->string('name');
//           $table->foreignId('eptool_id')->onDelete('cascade');
//           $table->timestamps();
//         });
//     }

// }




// class CreateGalleriesTable extends Migration
// {
//     /**
//      * Run the migrations.
//      *
//      * @return void
//      */
//     public function up()
//     {
//         Schema::create('galleries', function (Blueprint $table) {
//             $table->id();
//             $table->string('name');
//             $table->foreignId('user_id')->onDelete('cascade');
//             $table->timestamps();
//         });
//     }

//     /**
//      * Reverse the migrations.
//      *
//      * @return void
//      */
//     public function down()
//     {
//         Schema::dropIfExists('galleries');
//     }
// }

<?php


namespace Visanduma\LaravelInvoice\Tests\database;


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class TestTable extends Migration
{
    public function up()
    {
        Schema::create('test_models', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
        });
    }


    public function down()
    {
        Schema::dropIfExists('test_models');
    }

}

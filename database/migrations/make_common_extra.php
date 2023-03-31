<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MakeCommonExtra extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('laravel_invoice_extras', function (Blueprint $table) {
            $table->morphs('extra');
            $table->dropConstrainedForeignId('invoice_id');
            $table->dropColumn('invoice_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('laravel_invoice_extras', function (Blueprint $table) {
            $table->dropMorphs('extra');
            $table->unsignedBigInteger('invoice_id');

        });
    }
}

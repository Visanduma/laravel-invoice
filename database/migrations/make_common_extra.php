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
            $table->dropColumn(['invoice_id']);
            $table->morphs('model');
            $table->dropConstrainedForeignId('invoice_id');
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
            $table->dropMorphs('model');
            $table->unsignedBigInteger('invoice_id');

        });
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateInvoiceItems extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('laravel_invoice_items', function (Blueprint $table) {
            $table->string('invoiceItemable_type');
            $table->unsignedBigInteger('invoiceItemable_id');
            $table->index(['invoiceItemable_type', 'invoiceItemable_id'], 'invoice_itemable_index');
            $table->unsignedBigInteger('parent_item_id')->nullable()->after('id');
            $table->foreign('parent_item_id')->references('id')->on('laravel_invoice_items')->onDelete('cascade');
        });
        Schema::create('label_mappings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('label');
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
        Schema::table('laravel_invoice_items', function (Blueprint $table) {
            $table->dropMorphs('invoiceItemable');
            $table->dropForeign(['parent_item_id']);
            $table->dropColumn('parent_item_id');
        });
        Schema::dropIfExists('label_mappings');
    }
}

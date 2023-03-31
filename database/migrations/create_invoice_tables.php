<?php

use Faker\DefaultGenerator;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInvoiceTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('laravel_invoices', function (Blueprint $table) {
            $table->id();
            $table->nullableMorphs('invoiceable');
            $table->date('invoice_date');
            $table->date('due_date')->nullable();
            $table->string('tag')->nullable();
            $table->string('invoice_number');
            $table->string('ref_number')->nullable();
            $table->string('status');
            $table->string('paid_status');
            $table->text('note')->nullable();
            $table->decimal('discount')->default(0);
            $table->decimal('discount_value')->default(0);
            $table->string('discount_type')->nullable();
            $table->decimal('sub_total')->default(0);
            $table->decimal('total')->default(0);
            $table->decimal('due_amount')->default(0);
            $table->integer('created_by')->nullable();
            $table->timestamps();
        });


        Schema::create('laravel_invoice_extras', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('invoice_id');
            $table->string('key');
            $table->longText('value');
            $table->timestamps();

            $table->foreign('invoice_id')->references('id')->on('laravel_invoices')->onDelete('cascade');
        });

        Schema::create('laravel_invoice_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('invoice_id');
            $table->foreign('invoice_id')->references('id')->on('laravel_invoices')->onDelete('cascade');

            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('discount')->default(0);
            $table->decimal('discount_value')->default(0);
            $table->string('discount_type')->nullable();
            $table->decimal('price');
            $table->integer('qty');
            $table->string('unit')->nullable();
            $table->decimal('total');
            $table->integer('item_id')->nullable();
            $table->string('tag')->nullable();
            $table->timestamps();
        });


        Schema::create('laravel_invoice_payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('invoice_id');
            $table->foreign('invoice_id')->references('id')->on('laravel_invoices')->onDelete('cascade');

            $table->string('method');
            $table->decimal('amount');
            $table->text('note')->nullable();
            $table->date('payment_date');
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
        Schema::dropIfExists('laravel_invoices');
        Schema::dropIfExists('laravel_invoice_extras');
        Schema::dropIfExists('laravel_invoice_items');
        Schema::dropIfExists('laravel_invoice_payments');
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableStockInitial extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stock_initial', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->unsignedSmallInteger('reste') ;
            $table->unsignedBigInteger('produit')->index() ;
            $table->foreign('produit')->references('id')->on('produits')->onDelete('cascade') ;
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('stock_initial');
    }
}

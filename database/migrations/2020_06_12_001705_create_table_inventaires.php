<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableInventaires extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('inventaires', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->unsignedSmallInteger('moy_achat') ;
            $table->unsignedSmallInteger('moy_vente') ;
            $table->unsignedSmallInteger('quantite_vendue') ;
            $table->unsignedSmallInteger('quantite_achetee') ;
            $table->unsignedBigInteger('produit')->index();
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
        Schema::dropIfExists('inventaires');
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableApprovisionnements extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('approvisionnements', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->unsignedSmallInteger('prix_achat') ;
            $table->unsignedSmallInteger('prix_vente') ;
            $table->unsignedSmallInteger('quantite') ;
            $table->string('code',6) ;
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
        Schema::dropIfExists('approvisionnements');
    }
}

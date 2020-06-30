<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Inventaire extends Model
{
    protected $fillable = ['produit','moy_achat','moy_vente','quantite_vendue','quantite_achetee'] ;

    //relations de clés étrangères
    public function produit_linked(){
        return $this->belongsTo(Produit::class,'produit') ;
    }
}

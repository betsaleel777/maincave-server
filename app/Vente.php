<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Vente extends Model
{
    protected $fillable = ['produit','prix_achat','prix_vente','quantite','code','created_at'] ;
    const RULES = ['produit' => 'required',
                   'quantite' => 'required|numeric'
                ] ;
    const UPDATE_RULES = ['quantite' => 'required|numeric'] ;
    const MESSAGES = ['produit.required' => 'le choix du produit est requis',
                      'quantite.required' => 'la quantite est requise',
                      'quantite.numeric' => 'quantite est une valeur numérique'
                     ] ;
    //scopes
    public function scopeProcessing($query){
      return $query->where('examiner',true) ;
    }

    public function scopeClosed($query){
      return $query->where('fermer',true) ;
    }

    public function scopeOpened($query){
        return $query->where('fermer',false) ;
    }
    //ecrire le setter qui va générer le code
    public function immatriculer(){
        $lettres = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ' ;
        $chiffres = '0123456789' ;
        $this->attributes['code'] = substr(str_shuffle($lettres),15,3).substr(str_shuffle($chiffres),4,3) ;
    }

    //relations de clés étrangères
    public function produit_linked(){
        return $this->belongsTo(Produit::class,'produit') ;
    }
}

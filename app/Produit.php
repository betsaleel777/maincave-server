<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Produit extends Model
{
    protected $fillable = ['libelle','prix_achat','prix_vente'] ;

    const RULES = [ 'libelle' => 'required|max:150','prix_achat' => 'required|numeric' , 'prix_vente' => 'required|numeric' ] ;
    const MESSAGES = [ 'libelle.required' => 'le libellé est requis',
                       'libelle.max' => 'Nombre de caractère maximal dépassé' ,
                       'prix_achat.required' => 'le prix d\'achat est requis',
                       'prix_achat.numeric' => 'le prix d\'achat est une valeure numérique',
                       'prix_vente.required' => 'le prix de vente est requis',
                       'prix_vente.numeric' => 'le prix de vente est une valeure numérique'
                    ] ;
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Produit;

class ProduitsController extends Controller
{
    // get
    public function index(){
       $produits = Produit::orderBy('id','DESC')->get() ;
       return response()->json(['produits' => $produits]) ;
    }

    // post
    public function add(Request $request){
      $this->validate($request,Produit::RULES,Produit::MESSAGES);
      Produit::create($request->all());
      return response()->json(['message' => 'le produit '.$request->libelle.' a été crée avec succès !']) ;
    }

    // get
    public function edit(int $id){
      return response()->json(['produit' => Produit::findOrFail($id)]) ;
    }

    //post
    public function update(Request $request){
       $this->validate($request,Produit::RULES,Produit::MESSAGES);
       $produit = Produit::findOrFail($request->id) ;
       $produit->libelle = $request->libelle ;
       $produit->prix_achat = $request->prix_achat ;
       $produit->prix_vente = $request->prix_vente ;
       $produit->save() ;
       return response()->json(['message' => 'Modification du produit a été enregistrée avec succès !! ']);
    }

    public function trash(Request $request){
       $produit = Produit::findOrFail($request->id) ;
       $produit->delete() ;
       return response()->json(['message' => 'La suppression du produit '.$produit->libelle.' a été éffectuée avec succes !!']) ;
    }

}

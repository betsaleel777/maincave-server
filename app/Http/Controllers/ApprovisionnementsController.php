<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Produit ;
use App\Approvisionnement ;
use App\Etat ;

class ApprovisionnementsController extends Controller
{
    // get
    public function index(){
        if(empty(Etat::orderBy('id','DESC')->get()->first())){
          $traitement = false ;
        }else{
          $traitement = (bool)Etat::orderBy('id','DESC')->get()->first()->traitement ;
        }
        $approvisionnements = Approvisionnement::with('produit_linked')->orderBy('id','DESC')->get() ;
        return response()->json(['approvisionnements' => $approvisionnements,'traitement' => $traitement]) ;
     }

     public function openedPurchase(){
        $approvisionnements = Approvisionnement::with('produit_linked')->opened()->orderBy('id','DESC')->get() ;
        return response()->json(['approvisionnements' => $approvisionnements]) ;
    }

    public function closedPurchase(){
        $approvisionnements = Approvisionnement::with('produit_linked')->closed()->orderBy('id','DESC')->get() ;
        return response()->json(['approvisionnements' => $approvisionnements]) ;
    }

     // post
     public function add(Request $request){
       $this->validate($request,Approvisionnement::RULES,Approvisionnement::MESSAGES);
       $produit = Produit::findOrFail($request->produit) ;
       $approvisionnement = new Approvisionnement($request->all()) ;
       $approvisionnement->prix_achat = $produit->prix_achat ;
       $approvisionnement->prix_vente = $produit->prix_vente ;
       $approvisionnement->immatriculer() ;
       $approvisionnement->save() ;
       return response()->json(['message' => 'Achat '.$request->libelle.' a été enregistré avec succès !']) ;
     }

     // get
     public function edit(int $id){
       return response()->json(['approvisionnement' => Approvisionnement::findOrFail($id)]) ;
     }

     //post
     public function update(Request $request){
        $this->validate($request,Approvisionnement::UPDATE_RULES,Approvisionnement::MESSAGES);
        $approvisionnement = Approvisionnement::findOrFail($request->id) ;
        $approvisionnement->quantite = $request->quantite ;
        $approvisionnement->created_at = $request->date ;
        $approvisionnement->save() ;
        return response()->json(['message' => 'Modification de l\'achat a été enregistrée avec succès !! ']);
     }

     public function trash(Request $request){
        $approvisionnement = Approvisionnement::findOrFail($request->id) ;
        $approvisionnement->delete() ;
        return response()->json(['message' => 'La suppression de l\'achat '.$approvisionnement->code.' a été éffectuée avec succes !!']) ;
     }
}

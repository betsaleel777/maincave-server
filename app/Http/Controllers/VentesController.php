<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Produit ;
use App\Approvisionnement ;
use App\Etat ;
use App\Vente ;
use App\StockInitial ;

class VentesController extends Controller
{
    // get
    public function index(){
        if(empty(Etat::orderBy('id','DESC')->get()->first())){
          $traitement = false ;
        }else{
          $traitement = (bool)Etat::orderBy('id','DESC')->get()->first()->traitement ;
        }
        $ventes = Vente::with('produit_linked')->orderBy('id','DESC')->get() ;
        return response()->json(['ventes' => $ventes,'traitement' => $traitement]) ;
     }

    public function openedSell(){
        $ventes = Vente::with('produit_linked')->opened()->orderBy('id','DESC')->get() ;
        return response()->json(['ventes' => $ventes]) ;
    }

    public function closedSell(){
        $ventes = Vente::with('produit_linked')->closed()->orderBy('id','DESC')->get() ;
        return response()->json(['ventes' => $ventes]) ;
    }

     // post
     public function add(Request $request){
       $this->validate($request,Vente::RULES,Vente::MESSAGES);
       $reponse = $this->check($request);
       if($reponse['check']){
        $produit = Produit::findOrFail($request->produit) ;
        $vente = new Vente($request->all()) ;
        $vente->prix_achat = $produit->prix_achat ;
        $vente->prix_vente = $produit->prix_vente ;
        $vente->immatriculer() ;
        $vente->save() ;
        return response()->json(['message' => 'La vente du produit '.$request->libelle.' a été enregistré avec succès ! il reste '.$reponse['reste'].' en stock','variant' => 'success']) ;
       }else{
        return response()->json(['message' => $reponse['message'],'variant' => 'danger']) ;
       }
     }

     // get
     public function edit(int $id){
       return response()->json(['vente' => Vente::findOrFail($id)]) ;
     }

     // post
     public function update(Request $request){
        $this->validate($request,Vente::UPDATE_RULES,Vente::MESSAGES);
        $reponse = $this->check($request,true);
        if($reponse['check']){
            $vente = Vente::findOrFail($request->id) ;
            $vente->quantite = $request->quantite ;
            $vente->created_at = $request->date ;
            $vente->save() ;
            return response()->json(['message' => 'Modification de la vente: '.$request->code.' a été enregistrée avec succès !! il reste '.$reponse['reste'].' en stock','variant' => 'success']);
        }else{
            return response()->json(['message' => $reponse['message'],'variant' => 'danger']) ;
        }

     }

     public function check(Request $request,Bool $update = false){
       //somme des achat déjà effectués
       $achats_somme = 0 ;
       $achats_list = Approvisionnement::opened()->where('produit',$request->produit)->get() ;
       $stock = StockInitial::where('produit',$request->produit)->get();
       $achats_somme = empty($stock->all())?0:$stock->first()->reste ;
       foreach ($achats_list as $achat) {
         $achats_somme += $achat['quantite'] ;
       }
       $ventes_somme = 0 ;
       //somme des ventes déjà effectuées
       $ventes_list = Vente::opened()->where('produit',$request->produit)->get() ;
       if($update){
       //filtrer les ventes afin de retirer celle qui est concerné pour faire le test si c'est un update
         $id = $request->id ;
         $calebasse = array_filter($ventes_list->all(),function($vente) use($id){
           return !($vente['id'] === $id) ;
         }) ;
         $ventes_list = $calebasse ;
       }
       foreach ($ventes_list as $vente) {
         $ventes_somme += $vente['quantite'] ;
       }
       if(empty($achats_somme)){
           return ['check' => false, 'message' => 'Aucun achat a été enregistré pour ce produit au préalable, la vente est impossible'] ;
       }else{
           $reste_en_stock = $achats_somme - $ventes_somme ;
           if($reste_en_stock >= $request->quantite){
               return ['check' => true,'message' => 'vente valide !!','reste' => $reste_en_stock - $request->quantite] ;
           }else{
               return ['check' => false,'message' => 'vente invalide stock dépassé !!'] ;
           }
       }
     }

     public function productSellsChart(int $id){
      $ventes = Vente::with(['produit_linked' => function($query){
                              return $query->select('id','libelle') ;
                        }])->where(['produit' => $id,'fermer' => false])->get() ;
      return response()->json(['ventes' => $ventes]) ;
     }

     public function trash(Request $request){
        $vente = Vente::findOrFail($request->id) ;
        $vente->delete() ;
        return response()->json(['message' => 'La suppression de la vente '.$vente->code.' a été éffectuée avec succes !!']) ;
     }
}

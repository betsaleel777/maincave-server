<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use App\Approvisionnement ;
use App\Vente ;
use App\StockInitial ;
use App\Inventaire ;
use App\Etat ;
use Illuminate\Support\Carbon ;


class DashboardController extends Controller
{

    //les donneés nécessaire pour que le panneau de configuarion existe (depense total,vente total,produit qui sort le plus, produit le plus rentable)
    public function index(){
      //depense total
      $achats = Approvisionnement::select(DB::raw('sum(quantite*prix_achat::INTEGER) as achats'))->where('fermer',false)->get()->first()->achats ;
      //vente total
      $ventes = Vente::select(DB::raw('sum(quantite*prix_vente::INTEGER) as ventes'))->where('fermer',false)->get()->first()->ventes ;
      //produit qui sort le plus
      $mostWanted = Vente::selectRaw('produit,sum(quantite) as quantite')->with('produit_linked')->where('fermer',false)->groupBy('produit')->orderBy('quantite','DESC')->get()->first() ;
      //produit le plus rentable
      $mvp = Vente::selectRaw('produit,avg(prix_vente)-avg(prix_achat) as moy_marge,sum(quantite) as quantite,(avg(prix_vente)-avg(prix_achat))*sum(quantite) as montant')
                    ->with('produit_linked')->where('fermer',false)->groupBy('produit')->orderBy('montant','DESC')->get()->first() ;
      if(empty($mvp)){
          $montantMvp = 0 ;
          $nameMvp = 'AUCUN' ;
      }else{
        $montantMvp = $mvp->montant ;
        $nameMvp = $mvp->produit_linked->libelle ;
      }
      if(empty($mostWanted)){
        $quantityMostWanted = 0 ;
        $nameMostWanted = 'AUCUN' ;
      }else{
        $quantityMostWanted = $mostWanted->quantite ;
        $nameMostWanted = $mostWanted->produit_linked->libelle ;
      }
      return response()->json(['achats' => (int)$achats,
                               'ventes' => (int)$ventes,
                               'mvpName' => $nameMvp,
                               'mvpQuantity' => $montantMvp,
                               'mostWantedName' => $nameMostWanted,
                               'mostWantedQuantity' => $quantityMostWanted
                              ]) ;
    }

    public static function getInventory(){
      $inventaire = DB::select(DB::raw("with achats as (select p.id as produit,p.libelle,avg(a.prix_achat)::INTEGER as moy_achat, sum(quantite)
      as achat_unitaire from approvisionnements a inner join produits p on p.id=a.produit where a.fermer = false
      group by a.produit,p.id,p.libelle) select achats.produit,achats.libelle,achats.moy_achat,avg(ventes.prix_vente)::INTEGER as moy_vente,achats.achat_unitaire,
      sum(ventes.quantite) as vente_unitaire from ventes inner join achats on ventes.produit = achats.produit
      where ventes.fermer = false group by ventes.produit,achats.produit,achats.libelle,achats.moy_achat,achats.achat_unitaire
      union
      select p.id as produit,p.libelle,avg(a.prix_achat) as moy_achat,avg(a.prix_vente)::INTEGER as moy_vente,sum(quantite) as achat_unitaire,0 as vente_unitaire
      from approvisionnements a inner join produits p on p.id=a.produit where a.fermer = false and produit not in
      (select ventes.produit from ventes) group by a.produit,p.id,p.libelle")) ;

      //transforme l'inventaire en tableau
      $inventaire = json_decode(json_encode($inventaire), true);

      //ajout du stock initial
      $initial = StockInitial::get()->all() ;
      if(!empty($initial)){
        $calebasse = array_map(function($produit) use ($initial){
            foreach ($initial as $row) {
               if($produit['produit'] == $row['produit']){
                 $achat_new = $row['reste'] + $produit['achat_unitaire'] ;
                 return ['produit' => $produit['produit'],
                         'moy_vente' => $produit['moy_vente'],
                         'moy_achat' => $produit['moy_achat'],
                         'vente_unitaire' => $produit['vente_unitaire'],
                         'achat_unitaire' => $achat_new
                        ] ;
               }else{
                   return $produit ;
               }
            }
        },$inventaire) ;
        $inventaire = $calebasse ;
      }
      return $inventaire ;
    }

    //génère un tableau recapitulatif des achats et des ventes de la période en cours

    public function inventaire(){
     $inventaire = $this::getInventory() ;
     //met tous les achat et les ventes de la période en traitement
     if(!empty($inventaire)){
         Approvisionnement::where(['examiner'=> false,'fermer' => false])->update(['examiner' => true]) ;
         Vente::where(['examiner' => false, 'fermer' => false])->update(['examiner' => true]) ;
         Etat::create(['traitement' => true]) ;
     }
     return response()->json(['inventaires' => $inventaire]) ;
    }

    // tout les achats et vente en vérification d'inventaire deviennent innacceéssible en edition et suppression
    public function fermeture(){
       $inventaire = $this::getInventory() ;
       //enregistrement de l'inventaire en base
       $data = array_map(function($ligne){
         return ['produit' => $ligne['produit'],
                 'moy_vente' => $ligne['moy_vente'],
                 'moy_achat' => $ligne['moy_achat'],
                 'quantite_vendue' => $ligne['vente_unitaire'],
                 'quantite_achetee' => $ligne['achat_unitaire'],
                 'created_at' => Carbon::now(),
                 'updated_at' => Carbon::now()
                ] ;
       },$inventaire) ;
       Inventaire::insert($data) ;
       StockInitial::truncate() ; //on vide la table stock_initial
       //on rempli le stock initial avec les nouvelles valeures qui viennent du dernier inventaire
       $data = array_map(function($ligne){
         return ['produit' => $ligne['produit'],
                 'reste' => $ligne['achat_unitaire'] - $ligne['vente_unitaire'],
                 'created_at' => Carbon::now(),
                 'updated_at' => Carbon::now()
                ] ;
       },$inventaire) ;
       StockInitial::insert($data) ;
       //on verouille tout les achats et les ventes qui sont encore en examen
       Approvisionnement::where(['examiner'=> true,'fermer' => false])->update(['fermer' => true]) ;
       Vente::where(['examiner' => true, 'fermer' => false])->update(['fermer' => true]) ;
       Etat::create(['traitement' => false]) ;
       return response()->json(['message' => "la fermeture de cette période d'exercice a bien été effectuée."]) ;
    }

    public function getStatus(){
        if(empty(Etat::orderBy('id','DESC')->get()->first())){
          $traitement = false ;
        }else{
          $traitement = (bool)Etat::orderBy('id','DESC')->get()->first()->traitement ;
        }
        return response()->json(['traitement' => $traitement]) ;
    }

    public function annuler(){
        Approvisionnement::where(['examiner'=> true,'fermer' => false])->update(['examiner' => false]) ;
        Vente::where(['examiner' => true, 'fermer' => false])->update(['examiner' => false]) ;
        Etat::create(['traitement' => false]) ;
        return response()->json(['message' => "l'opération d'annulation a été effectuée avec succès,Vous pouvez de nouveau ajouter des approvisionnements et des ventes." ]) ;
    }

    public function datesInventaire(){
        $dates = Inventaire::select('created_at')->distinct()->get()->all() ;
        $calebasse = array_map(function($date){
            $carbone_date = new Carbon($date->created_at) ;
            $label = $carbone_date->format('d-m-Y') ;
            return ['code' => $date->created_at,'label' => $label ] ;
        },$dates) ;
        $dates = $calebasse ;
        return response()->json(['dates' => $dates]) ;
    }

    public function oldInventaire(string $date){
      $date = new Carbon($date) ;
      $date = $date->format('Y-m-d') ;
      $inventaire = Inventaire::with('produit_linked')->whereRaw('created_at::DATE = ?',[$date])->get() ;
      return response()->json(['inventaires' => $inventaire,'date' => $date]) ;
    }

}

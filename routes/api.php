<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/produits','ProduitsController@index')->name('produits') ;
Route::post('/produit/add','ProduitsController@add')->name('produit_add') ;
Route::get('/produit/{id}','ProduitsController@edit')->name('produit_edit') ;
Route::post('/produit/update','ProduitsController@update')->name('produit_update') ;
Route::post('/produit/trash','ProduitsController@trash')->name('produit_trash') ;

Route::get('/approvisionnements','ApprovisionnementsController@index')->name('approvisionnements') ;
Route::post('/approvisionnement/add','ApprovisionnementsController@add')->name('approvisionnement_add') ;
Route::get('/approvisionnement/{id}','ApprovisionnementsController@edit')->name('approvisionnement_edit') ;
Route::get('/approvisionnement/ouvert','ApprovisionnementsController@openedPurchase')->name('approvisionnement_ouvert') ;
Route::get('/approvisionnement/fermer','ApprovisionnementsController@closedPurchase')->name('approvisionnement_fermer') ;
Route::post('/approvisionnement/update','ApprovisionnementsController@update')->name('approvisionnement_update') ;
Route::post('/approvisionnement/trash','ApprovisionnementsController@trash')->name('approvisionnement_trash') ;

Route::get('/ventes','VentesController@index')->name('ventes') ;
Route::post('/vente/add','VentesController@add')->name('vente_add') ;
Route::get('/vente/{id}','VentesController@edit')->name('vente_edit') ;
Route::get('/ventes/produit/{id}','VentesController@productSellsChart')->name('chart_produits_vendus') ;
Route::get('/vente/ouvert','VentesController@openedSell')->name('vente_ouvert') ;
Route::get('/vente/fermer','VentesController@closedSell')->name('vente_fermer') ;
Route::post('/vente/update','VentesController@update')->name('vente_update') ;
Route::post('/vente/trash','VentesController@trash')->name('vente_trash') ;
// Route::post('/vente/check','VentesController@check')->name('vente_check') ;

Route::get('/inventaire/dates','DashboardController@dates')->name('dates_list') ;

Route::get('/dashboard/utils','DashboardController@index')->name('dashboard_utils') ;
Route::get('/dashboard/inventaire','DashboardController@inventaire')->name('dashboard_inventaire') ;
Route::get('/dashboard/inventaire/old/{date}','DashboardController@oldInventaire')->name('dashboard_inventaire_old') ;
Route::get('/dashboard/dates','DashboardController@datesInventaire')->name('dashboard_dates') ;
Route::get('/dashboard/status/examen','DashboardController@getStatus')->name('dashboard_status') ;
Route::get('/dashboard/fermeture', 'DashboardController@fermeture')->name('dashboard_fermeture') ;
Route::get('/dashboard/annuler', 'DashboardController@annuler')->name('dashboard_annuler') ;

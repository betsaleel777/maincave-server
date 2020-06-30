<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class StockInitial extends Model
{
    protected $table = 'stock_initial' ;
    protected $fillable = ['reste','produit'] ;
}

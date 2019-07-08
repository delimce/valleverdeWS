<?php

namespace App\Models\Wholesales;


use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Order extends Model
{

    protected $connection = 'wholesale';
    protected $table = "carrito";
    protected $primaryKey = 'co_ven';

    public $timestamps = false;

    ///foreing key
    public function salesman()
    {
        return $this->belongsTo('App\Models\Wholesales\Salesman', 'co_ven', 'co_ven');
    }

    ///foreing key
    public function customer()
    {
        return $this->belongsTo('App\Models\Wholesales\Customer', 'co_cli', 'co_cli');
    }

    public function product()
    {
        return $this->hasMany('App\Models\Wholesales\OrderProduct', 'f_cart_id', 'cart_id');
    }

    public function date()
    {
        return Carbon::parse($this->fecha)->format('Y-m-d');
    }


}
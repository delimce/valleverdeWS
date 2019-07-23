<?php
/**
 * Created by PhpStorm.
 * User: ldeLima
 * Date: 14/12/2015
 * Time: 16:14
 */

namespace App\Models\Wholesales;

use function Clue\StreamFilter\fun;
use Illuminate\Database\Eloquent\Model;

class OrderProduct extends Model
{

    protected $connection = 'wholesale';
    protected $table = "carrito_producto";
    protected $primaryKey = ['f_cart_id', 'renglon'];
    public $incrementing = false;
    public $timestamps = false;

    ///foreing key
    public function order()
    {
        return $this->belongsTo('App\Models\Wholesales\Order', 'f_cart_id', 'cart_id');
    }

    protected function getCode()
    {
        return $this->co_lin . $this->co_sub . $this->co_col;
    }

    protected function getSizes()
    {
        return json_decode($this->cant_txt, true);
    }

    public function getProductsDetails()
    {
        $details = [];
        $sizes = $this->getSizes();
        foreach ($sizes as $key => $cant) {
            $pre_key = explode("_", $key);
            $price_unit = number_format(
                $this->pvp, 2, '.', ''
            );
            $price_total = number_format(
                $this->pvp * $cant, 2, '.', ''
            );
            ///type;code;cant;price unit;price total;
            $details[] = $this->tipo_prod . ';' . $this->getCode() . $pre_key[1] . ';'
                . $cant . ';' . $price_unit . ';' . $price_total . ';' . $this->codigobarra;
        }
        return $details;
    }


//    public function stock(){
//        return $this->hasMany('App\Models\Product\Stock', 'sku', 'sku');
//    }


}
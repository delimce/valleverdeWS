<?php
/**
 * Created by PhpStorm.
 * User: ldeLima
 * Date: 14/12/2015
 * Time: 16:14
 */

namespace App\Models\Order;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;

class OrderProduct extends Model
{

    protected $table = "oc_order_product";
    protected $primaryKey = 'order_product_id';
    public $timestamps = false;

    ///foreing key
    public function orden()
    {
        return $this->belongsTo('App\Models\Order\Order', 'order_id');
    }

    public function stock(){
        return $this->hasMany('App\Models\Product\Stock', 'sku', 'sku');
    }


    public function getWeightBySize()
    {

        $size = Str::substr($this->sku, -2); //size
        $line = Str::substr($this->sku, 0, 3); // line

        $bigbox = 0.780;
        $smallbox = 0.450;

        $real = intval($size);
        $sandalia = 'SASA';//sandalia anatomica

        if ($real > 27 && $line != $sandalia) { //big box
            $finalWeight = $bigbox;
        } else if ($real == 28 && $line == $sandalia) { //small box
            $finalWeight = $smallbox;
        } else { ///small box
            $finalWeight = $smallbox;
        }

        return $this->quantity*$finalWeight; //quantity * weight


    }

}
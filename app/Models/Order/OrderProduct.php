<?php
/**
 * Created by PhpStorm.
 * User: ldeLima
 * Date: 14/12/2015
 * Time: 16:14
 */

namespace App\Models\Order;


use Illuminate\Database\Eloquent\Model;

class OrderProduct extends Model
{

    protected $table = "oc_order_product";
    protected $primaryKey = 'order_product_id';
    public $timestamps = false;

    ///foreing key
    public function orden()
    {
        return $this->belongsTo('App\Models\Order\Order','order_id');
    }

}
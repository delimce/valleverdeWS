<?php
/**
 * Created by PhpStorm.
 * User: delimce
 * Date: 9/27/2018
 * Time: 7:38 PM
 */

namespace App\Models\Order;
use Illuminate\Database\Eloquent\Model;

class OrderTotal extends Model
{

    protected $table = "oc_order_total";
    protected $primaryKey = 'order_total_id';
    public $timestamps = false;

    ///foreing key
    public function orden()
    {
        return $this->belongsTo('App\Models\Order\Order','order_id');
    }
}
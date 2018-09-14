<?php
/**
 * Created by PhpStorm.
 * User: ldelima
 * Date: 10/9/18
 * Time: 12:15 PM
 */

namespace App\Models\Order;
use Illuminate\Database\Eloquent\Model;

class Order  extends Model
{

    protected $table = "oc_order";
    protected $primaryKey = 'order_id';
    public $timestamps = false;

    ///foreing key
    public function customer()
    {
        return $this->belongsTo('App\Models\Customer\Customer','customer_id');
    }

    public function history() {
        return $this->hasMany('App\Models\Order\OrderHistory', 'order_id');
    }

}
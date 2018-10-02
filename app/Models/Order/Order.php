<?php
/**
 * Created by PhpStorm.
 * User: ldelima
 * Date: 10/9/18
 * Time: 12:15 PM
 */

namespace App\Models\Order;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

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

    public function product() {
        return $this->hasMany('App\Models\Order\OrderProduct', 'order_id');
    }

    public function totals() {
        return $this->hasMany('App\Models\Order\OrderTotal', 'order_id');
    }

    public function date(){
        return Carbon::parse($this->date_added)->format('Y-m-d');
    }

}
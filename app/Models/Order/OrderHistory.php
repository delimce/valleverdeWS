<?php
/**
 * Created by PhpStorm.
 * User: ldeLima
 * Date: 14/12/2015
 * Time: 15:50
 */

namespace app\Models;

use Illuminate\Database\Eloquent\Model;

class OrderHistory extends Model
{

    protected $table = "oc_order_history";
    protected $primaryKey = 'order_history_id';
    public $timestamps = false;

    ///foreing key
    public function orden()
    {
        return $this->belongsTo('App\Models\Order\Order','order_id');
    }


}
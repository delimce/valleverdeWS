<?php
/**
 * Created by PhpStorm.
 * User: ldeLima
 * Date: 14/12/2015
 * Time: 16:14
 */

namespace App\Models\Order;


use Illuminate\Database\Eloquent\Model;

class CredicardTransfer extends Model
{

    protected $table = "op_credicard_transfer";


    ///foreing key
    public function order()
    {
        return $this->belongsTo('App\Models\Order\Order', 'order_id');
    }


}
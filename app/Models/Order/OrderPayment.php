<?php
/**
 * Created by PhpStorm.
 * User: ldelima
 * Date: 10/9/18
 * Time: 12:15 PM
 */

namespace App\Models\Order;
use Illuminate\Database\Eloquent\Model;

class OrderPayment  extends Model
{

    protected $table = "op_order_payment";
    protected $primaryKey = 'payment_id';
    public $timestamps = false;

    ///foreing key
    public function orden()
    {
        return $this->belongsTo('App\Models\Order\Order', 'order_id');
    }


}
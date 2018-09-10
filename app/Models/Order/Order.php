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

    protected $table = "oc_order";
    protected $primaryKey = 'order_id';
    public $timestamps = false;


}
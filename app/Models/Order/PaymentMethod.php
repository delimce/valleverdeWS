<?php
/**
 * Created by PhpStorm.
 * User: ldeLima
 * Date: 14/12/2015
 * Time: 15:50
 */

namespace App\Models\Order;

use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{

    protected $table = "op_payment_method";
    protected $primaryKey = 'payment_method_id';


}
<?php
/**
 * Created by PhpStorm.
 * User: ldeLima
 * Date: 14/12/2015
 * Time: 16:14
 */

namespace App\Models\Customer;


use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{

    protected $table = "oc_customer";
    protected $primaryKey = 'customer_id';

    public $timestamps = false;


    public function order() {
        return $this->hasMany('App\Models\Order\Order', 'customer_id');
    }

}
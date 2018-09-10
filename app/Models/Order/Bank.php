<?php
/**
 * Created by PhpStorm.
 * User: ldeLima
 * Date: 14/12/2015
 * Time: 16:14
 */

namespace App\Models\Order;


use Illuminate\Database\Eloquent\Model;

class Bank extends Model
{

    protected $table = "op_bank";
    protected $primaryKey = 'bank_id';

    protected $visible = array('bank_id','name','active'); ///only fields that return



    public function account() {
        return $this->hasMany('App\Models\Order\BankAccount', 'bank_id');
    }

}
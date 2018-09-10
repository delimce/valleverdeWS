<?php
/**
 * Created by PhpStorm.
 * User: ldeLima
 * Date: 14/12/2015
 * Time: 16:14
 */

namespace App\Models\Order;


use Illuminate\Database\Eloquent\Model;

class BankAccount extends Model
{

    protected $table = "op_bank_account";
    protected $primaryKey = 'id';

    protected $visible = array('id','number','name'); ///only fields that return


    ///foreing key
    public function bank()
    {
        return $this->belongsTo('App\Models\Order\Bank', 'bank_id');
    }




}
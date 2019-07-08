<?php
/**
 * Created by PhpStorm.
 * User: ldeLima
 * Date: 14/12/2015
 * Time: 16:14
 */

namespace App\Models\Wholesales;


use Illuminate\Database\Eloquent\Model;

class Salesman extends Model
{

    protected $connection = 'wholesale';
    protected $table = "vendedor";
    protected $primaryKey = 'co_ven';

    public $timestamps = false;




}
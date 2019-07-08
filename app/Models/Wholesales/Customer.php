<?php
/**
 * Created by PhpStorm.
 * User: ldeLima
 * Date: 14/12/2015
 * Time: 16:14
 */

namespace App\Models\Wholesales;


use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{

    protected $connection = 'wholesale';
    protected $table = "cliente";
    protected $primaryKey = 'co_cli';

    public $timestamps = false;



}
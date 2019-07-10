<?php
/**
 * Created by PhpStorm.
 * User: ldeLima
 * Date: 14/12/2015
 * Time: 16:14
 */

namespace App\Models\Wholesales;


use Illuminate\Database\Eloquent\Model;

class Sync extends Model
{

    protected $connection = 'wholesale';
    protected $table = "sincro_log";
    protected $primaryKey = 'co_sync';

    public $timestamps = false;


}
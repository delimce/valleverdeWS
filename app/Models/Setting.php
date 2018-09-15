<?php
/**
 * Created by PhpStorm.
 * User: ldeLima
 * Date: 14/12/2015
 * Time: 16:14
 */

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{

    protected $table = "oc_setting";
    protected $primaryKey = 'setting_id';
    public $timestamps = false;

    protected $visible = array('code','key','value'); ///only fields that return



}
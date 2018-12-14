<?php
/**
 * Created by PhpStorm.
 * User: delimce
 * Date: 10/3/2018
 * Time: 6:34 PM
 */

namespace App\Models\Product;

use Illuminate\Database\Eloquent\Model;

class OptionValue extends Model
{
    protected $table = "oc_option_value";
    protected $primaryKey = 'option_value_id';
    public $timestamps = false;


    public function description()
    {
        return $this->hasOne('App\Models\Product\OptionValueDescription', 'option_value_id');
    }



}
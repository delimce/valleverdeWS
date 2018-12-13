<?php
/**
 * Created by PhpStorm.
 * User: delimce
 * Date: 10/3/2018
 * Time: 6:34 PM
 */

namespace App\Models\Product;

use Illuminate\Database\Eloquent\Model;

class ProductOption extends Model
{
    protected $table = "oc_product_option";
    protected $primaryKey = 'product_option_id';
    public $timestamps = false;

    public function product()
    {
        return $this->belongsTo('App\Models\Product\Product','product_id');
    }

    public function value()
    {
        return $this->hasMany('App\Models\Product\ProductOptionValue', 'product_option_id');
    }



}
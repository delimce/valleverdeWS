<?php
/**
 * Created by PhpStorm.
 * User: delimce
 * Date: 10/3/2018
 * Time: 6:34 PM
 */

namespace App\Models\Product;

use Illuminate\Database\Eloquent\Model;

class ProductOptionValue extends Model
{
    protected $table = "oc_product_option_value";
    protected $primaryKey = 'product_option_value_id';
    public $timestamps = false;

    public function option()
    {
        return $this->belongsTo('App\Models\Product\ProductOption','product_option_id');
    }


}
<?php
/**
 * Created by PhpStorm.
 * User: delimce
 * Date: 10/3/2018
 * Time: 6:34 PM
 */

namespace App\Models\Product;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $table = "oc_product";
    protected $primaryKey = 'product_id';
    public $timestamps = false;

    public function description() {
        return $this->hasOne('App\Models\Product\ProductDescription', 'product_id','product_id');
    }

    protected $visible = ['description','sku','image','quantity','price','price2','price3','price4','tax_class_id','weight','length','width','height','viewed','status'];

}
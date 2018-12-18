<?php
/**
 * Created by PhpStorm.
 * User: delimce
 * Date: 10/3/2018
 * Time: 6:34 PM
 */

namespace App\Models\Product;

use Illuminate\Database\Eloquent\Model;

class ProductStore extends Model
{
    protected $table = "oc_product_to_store";
    protected $primaryKey = 'product_id';
    public $timestamps = false;

    protected $visible = ['product_id','store_id'];

    public function product()
    {
        return $this->belongsTo('App\Models\Product\Product','product_id');
    }

}
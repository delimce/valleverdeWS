<?php
/**
 * Created by PhpStorm.
 * User: delimce
 * Date: 10/3/2018
 * Time: 6:34 PM
 */

namespace App\Models\Product;

use Illuminate\Database\Eloquent\Model;

class ProductDescription extends Model
{
    protected $table = "oc_product_description";
    protected $primaryKey = 'product_id';
    public $timestamps = false;

    protected $visible = ['name','description','tag','meta_title'];

    public function product()
    {
        return $this->belongsTo('App\Models\Product\Product','product_id');
    }

}
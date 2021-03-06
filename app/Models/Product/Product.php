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
    public    $timestamps = false;


    public function description()
    {
        return $this->hasOne('App\Models\Product\ProductDescription', 'product_id', 'product_id');
    }


    public function category()
    {
        return $this->hasMany('App\Models\Product\ProductCategory', 'product_id');
    }


    public function store()
    {
        return $this->hasMany('App\Models\Product\ProductStore', 'product_id');
    }


    public function option()
    {
        return $this->hasMany('App\Models\Product\ProductOption', 'product_id');
    }


    public function optionValue()
    {
        return $this->hasMany('App\Models\Product\ProductOptionValue', 'product_id');
    }


    public function setDimentions()
    {
        $this->weight = 0.5;
        $this->length = 13.0;
        $this->width  = 10.0;
        $this->height = 9.0;
    }


    protected $visible = [
        'description',
        'sku',
        'image',
        'quantity',
        'price',
        'price2',
        'price3',
        'price4',
        'tax_class_id',
        'weight',
        'length',
        'width',
        'height',
        'viewed',
        'status'
    ];

}
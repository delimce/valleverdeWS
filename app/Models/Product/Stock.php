<?php
/**
 * Created by PhpStorm.
 * User: delimce
 * Date: 10/3/2018
 * Time: 6:34 PM
 */

namespace App\Models\Product;

use Illuminate\Database\Eloquent\Model;

class Stock extends Model
{
    protected $table = "op_stock";
    protected $primaryKey = 'stock_id';
    public $timestamps = false;


    public function getProduct()
    {
        $sku = $this->co_lin . $this->model;
        return Product::whereSku($sku)->first();
    }


}
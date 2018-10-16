<?php
/**
 * Created by PhpStorm.
 * User: delimce
 * Date: 10/3/2018
 * Time: 6:57 PM
 */

namespace App\Http\Controllers\Api;

use App\Models\Product\Product;
use Laravel\Lumen\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use Validator;

class StockController extends BaseController
{

    public function getProduct($productId)
    {
        $product = Product::whereProductId($productId)->with('description')->first();

        return response()->json(['status' => 'ok', 'data' => $product]);
    }

    public function getProductBySku($sku)
    {
        $product = Product::whereSku($sku)->with('description')->first();

        return response()->json(['status' => 'ok', 'data' => $product]);
    }



}
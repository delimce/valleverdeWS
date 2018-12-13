<?php
/**
 * Created by PhpStorm.
 * User: delimce
 * Date: 10/3/2018
 * Time: 6:57 PM
 */

namespace App\Http\Controllers\Api;

use App\Models\Product\Category;
use App\Models\Product\Product;
use App\Models\Product\ProductCategory;
use App\Models\Product\ProductDescription;
use App\Models\Product\Stock;
use Laravel\Lumen\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use Validator;
use Carbon\Carbon;
use DB;

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


    public function updateProductList()
    {
        $products = Stock::getMainProducts();
        $main     = Product::all();
        $toInsert = [];
        foreach ($products as $prod) {
            $myProduct = $main->filter(
                function ($item) use ($prod) {
                    return $item->sku === $prod["sku"];
                }
            )->first();

            if (empty($myProduct)) { ///se debe crear el producto
                $this->createProduct($prod);
                $toInsert[] = $prod;
            }

        }

        return response()->json(['status' => 'ok', 'data' => $toInsert]);
    }


    private function createProduct($prod)
    {
        DB::beginTransaction();
        ///product
        $product                  = new Product();
        $product->sku             = $prod["sku"];
        $product->model           = $prod["desc"];
        $product->image           = $prod["image"];
        $product->manufacturer_id = 11;
        $product->shipping        = 1;
        $product->tax_class_id    = 10;
        $product->date_added      = Carbon::now();
        $product->date_modified   = Carbon::now();
        $product->status          = 0;
        //todo: dimensiones del producto y peso
        $product->save();
        ///product description
        $description              = new ProductDescription();
        $description->language_id = 2;
        $description->name        = $prod["desc"];
        $product->description()->save($description);
        ///product category
        $cat                  = Category::whereCode($prod["cat"])->first();
        $prodCat              = new ProductCategory();
        $prodCat->category_id = $cat->category_id;
        $product->category()->save($prodCat);


        DB::commit();
    }


}
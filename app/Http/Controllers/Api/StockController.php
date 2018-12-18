<?php
/**
 * Created by PhpStorm.
 * User: delimce
 * Date: 10/3/2018
 * Time: 6:57 PM
 */

namespace App\Http\Controllers\Api;

use App\Models\Product\Category;
use App\Models\Product\OptionValue;
use App\Models\Product\OptionValueDescription;
use App\Models\Product\Product;
use App\Models\Product\ProductCategory;
use App\Models\Product\ProductDescription;
use App\Models\Product\ProductOption;
use App\Models\Product\ProductOptionValue;
use App\Models\Product\Stock;
use Laravel\Lumen\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use Validator;
use Carbon\Carbon;
use DB;
use Log;

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

        try {
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
            ///asociar colores y tallas del producto

            $option_color             = new ProductOption();
            $option_color->product_id = $product->product_id;
            $option_color->option_id  = 13;
            $option_color->value      = '';
            $option_color->required   = 1;
            $option_color->save();
            $colorId = $option_color->product_option_id; //product_option_id COLOR

            $option_size             = new ProductOption();
            $option_size->product_id = $product->product_id;
            $option_size->option_id  = 14;
            $option_size->value      = '';
            $option_size->required   = 1;
            $option_size->save();
            $sizeId = $option_size->product_option_id; //product_option_id TALLA

            ///creando colores
            array_filter(
                $prod["colors"], function ($item) use ($prod, $product) {
                if ($item['desc']) {
                    $optionValue            = new OptionValue();
                    $optionValue->option_id = 13;
                    $optionValue->image     = 'catalog/products/' . $prod['sku'] . $item['cod'] . '.jpg';
                    $optionValue->codigo    = $item['cod'];
                    $optionValue->cod_largo = $prod['sku'];
                    $optionValue->id_produc = $product->product_id;
                    $optionValue->save();
                    $description              = new OptionValueDescription();
                    $description->language_id = 2;
                    $description->option_id   = 13;
                    $description->name        = $item['desc'];
                    $optionValue->description()->save($description);
                }
            }
            );


            //colores
            $colors = DB::table('oc_option_value')
                        ->select('option_value_id')
                        ->where('option_id', 13)
                        ->where('cod_largo', $prod["sku"])
                        ->whereIn('codigo', $prod["colors"])
                        ->get();
            $colors = $colors->toArray();

            ///tallas
            $sizes = DB::table('oc_option_value_description')
                       ->select('option_value_id')
                       ->where('option_id', 14)
                       ->whereIn('name', $prod["sizes"])
                       ->get();
            $sizes = $sizes->toArray();

            ///creando opciones de productos y tallas
            $my_colors = [];
            array_filter(
                $colors, function ($item) use ($colorId, &$my_colors) {
                $option_value                    = new ProductOptionValue();
                $option_value->option_id         = 13;
                $option_value->quantity          = 100;
                $option_value->product_option_id = $colorId;
                $option_value->option_value_id   = $item->option_value_id;
                $option_value->save();
            }
            );

            $my_sizes = [];
            array_filter(
                $sizes, function ($item) use ($sizeId, &$my_sizes) {
                $option_value                    = new ProductOptionValue();
                $option_value->option_id         = 14;
                $option_value->quantity          = 100;
                $option_value->product_option_id = $sizeId;
                $option_value->option_value_id   = $item->option_value_id;
                $option_value->save();
            }
            );

            DB::commit();

        } catch (\Exception $e) {
            DB::rollback();
            Log::error("IError: " . $e->getMessage());
            Log::error("Imposible crear el producto: " . $prod["desc"]);
            // something went wrong
        }


        return $colors;

    }


}
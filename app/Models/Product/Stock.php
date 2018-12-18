<?php
/**
 * Created by PhpStorm.
 * User: delimce
 * Date: 10/3/2018
 * Time: 6:34 PM
 */

namespace App\Models\Product;

use Illuminate\Database\Eloquent\Model;
use DB;
use App\Models\Product\Product;
use Cache;

class Stock extends Model
{
    protected $table      = "op_stock";
    protected $primaryKey = 'stock_id';
    public    $timestamps = false;


    public function getProductSku()
    {
        return $this->co_lin . $this->model;
    }


    public function getProduct()
    {
        return Product::whereSku($this->getProductSku())->first();
    }


    public static function getMainProducts($availiable = false)
    {
        $filter = ($availiable) ? " and s.cancel = 'N'" : ""; ///NO toma en cuenta los productos inactivos
        $query  = "SELECT DISTINCT
                    stock_id,
                    co_lin,
                    concat(s.co_lin,model) as sku,
                    s.`desc`,
                    max(s.price) as price,
                    sum(s.quantity) as quantity,
                    concat(s.co_lin,s.model,s.color,'.jpg') as image,
                    GROUP_CONCAT(distinct s.color order by c.co_col) as colors,
                    GROUP_CONCAT(distinct s.size) as sizes
                    FROM
                    op_stock AS s left join op_color c on s.color = c.co_col
                    where stock_id not in (3398,3399,3395,3396,3401,3397,3400,9528,9529,9530) $filter
                    GROUP BY co_lin,model";

        $result   = DB::select(DB::raw($query));
        $products = [];
        foreach ($result as $res) {
            $temp       = [
                "sku"      => $res->sku,
                "cat"      => $res->co_lin,
                "desc"     => self::format_desc($res->desc),
                "price"    => $res->price,
                "quantity" => $res->quantity,
                "image"    => 'catalog/products/' . $res->image,
                "colors"   => self::merge_arrays(explode(",", $res->colors)),
                "sizes"    => explode(",", $res->sizes)
            ];
            $products[] = $temp;
        }

        return $products;
    }


    static function getColors()
    {
        $colors = Cache::remember(
            'stock_colors', 5, function () { ///12 horas de cache
            $data = DB::table('op_color')->select('co_col', 'col_des')->get();;

            return json_decode($data, true);
        }
        );

        return $colors;
    }


    static function merge_arrays($array1)
    {
        $arrayMerged = [];
        $colors      = self::getColors();
        foreach ($array1 as $i => $item) {
            $name          = self::find_color_desc($item, $colors);
            $temp          = ["cod" => $item, "desc" => $name];
            $arrayMerged[] = $temp;
        }

        return $arrayMerged;
    }


    static function find_color_desc($needle, $array)
    {
        $result = false;
        foreach ($array as $item) {
            if ($needle == $item["co_col"]) {
                $result = $item["col_des"];
                break;
            }
        }

        return $result;
    }


    static function format_desc($desc)
    {
        $full  = explode(" ", $desc);
        $final = $full[0] . ' ' . $full[1];
        $final .= (strtolower($full[2]) != 'en') ? ' ' . $full[2] : '';

        return $final;
    }


}
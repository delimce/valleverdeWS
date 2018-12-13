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


    public static function getMainProducts()
    {

        $query = "SELECT DISTINCT
                    stock_id,
                    co_lin,
                    concat(s.co_lin,model) as sku,
                    s.`desc`,
                    concat(s.co_lin,s.model,s.color,'.jpg') as image,
                    -- count(*) as total,
                    GROUP_CONCAT(distinct s.color) as colors,
                    GROUP_CONCAT(distinct s.size) as sizes
                    FROM
                    op_stock AS s left join op_color c on s.color = c.co_col
                    where stock_id not in (3398,3399,3395,3396,3401,3397,3400,9528,9529,9530)
                    GROUP BY co_lin,model";

        $result   = DB::select(DB::raw($query));
        $products = [];

        foreach ($result as $res) {
            $temp       = [
                "sku"    => $res->sku,
                "cat"    => $res->co_lin,
                "desc"   => self::format_desc($res->desc),
                "image"  => $res->image,
                "colors" => explode(",", $res->colors),
                "sizes"  => explode(",", $res->sizes)
            ];
            $products[] = $temp;

        }

        return $products;

    }


    static function format_desc($desc)
    {
        $full  = explode(" ", $desc);
        $final = $full[0] . ' ' . $full[1];
        $final .= (strtolower($full[2]) != 'en') ? $full[2] : '';

        return $final;
    }


}
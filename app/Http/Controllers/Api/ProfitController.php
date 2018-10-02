<?php
/**
 * Created by PhpStorm.
 * User: ldelima
 * Date: 2/10/18
 * Time: 1:57 PM
 */

namespace App\Http\Controllers\Api;

use App\Models\Order\Order;
use Laravel\Lumen\Routing\Controller as BaseController;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Validator;
use Cache;


class ProfitController extends BaseController
{

    public function getOrdersPaid(){

        //ordenes en estatus pago procesado
        $orders = Order::whereOrderStatusId(2)->with('product')->get();

        $orders->each(function ($item) {

            try{
                $orderDetail = array();
                $orderDetail['order_id'] = $item->order_id;
                $orderDetail['order_date'] = $item->date();
                $orderDetail['customer_rif'] = $item->customer->rif;
                $prods = $item->product()->get();
                $prods->each(function ($prod) use($orderDetail) {
                    print $orderDetail['order_id'].';'. $orderDetail['order_date'].';'.$orderDetail['customer_rif'].';'; //order header
                    print $prod->sku.';'.$prod->quantity.';'.$prod->price.';'.$prod->quantity*$prod->price.';'; //products detail
                });

            }catch (\Exception $ex){
               return true; //continue
            }
            print "\n";

        });


    }

}
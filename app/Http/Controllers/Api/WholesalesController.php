<?php

namespace App\Http\Controllers\Api;

use App\Models\Wholesales\Customer;
use App\Models\Wholesales\Order;
use Laravel\Lumen\Routing\Controller as BaseController;
use DB;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Validator;
use Log;

class WholesalesController extends BaseController
{

    public function index()
    {
        $all = Customer::All();
        return $all;
    }

    public function getOrdersStock()
    {

        //ordenes sin sincronizarse al mayor
        $orders = Order::whereSync(0)->with(['product', 'customer', 'salesman'])->get();
        $orders->each(
            function ($item) {
                try {
                    $orderDetail = '';
                    $orderDetail .= $item->cart_id . ';';
                    $orderDetail .= $item->date() . ';';
                    $orderDetail .= $item->customer->cli_rif . ';';
                    $orderDetail .= strtoupper(ProfitController::transliterateString($item->customer->cli_des)) . ';';
                    $orderDetail .= strtoupper(ProfitController::transliterateString($item->customer->direc)) . ';';
                    $orderDetail .= $item->customer->telef . ';';
                    $shipping = null;

                    ///products details
                    $prods = $item->product()->get();
                    $prods->each(
                        function ($prod) use ($orderDetail) {
                            $details = $prod->getProductsDetails();
                            array_walk($details, function ($item) use($orderDetail){
                                print $orderDetail;
                                print $item;
                                print "\n";
                            });
                        }
                    );

                } catch (\Exception $ex) {
                    Log::error("Profit: error obteniendo orden:" . $item->cart_id, $ex->getTrace());
                    return true; //continue
                }
            });

    }

}

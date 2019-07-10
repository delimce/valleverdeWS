<?php

namespace App\Http\Controllers\Api;

use App\Models\Wholesales\Customer;
use App\Models\Wholesales\Order;
use App\Models\Wholesales\Sync;
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
            function (Order $item) {
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

    /**
     * set orders sync
     * @param $orders
     * @return \Illuminate\Http\JsonResponse
     */
    public function setOrderProcessed($orders)
    {
        $orders = explode(",", $orders);
        $success = [];
        $sync = new Sync();
        $sync->co_date_ini = Carbon::now();

        DB::beginTransaction();
        foreach ($orders as $orderId) {
            try {
                $order = Order::find($orderId);
                if ($order != null) {
                    $order->sync = 1; //synchronized
                    $order->save();
                     $success[] = ["order" => $order->cart_id, "processed" => true];
                } else {
                    $success[] = ["order" => $orderId, "processed" => false];
                    continue;
                }
            } catch (\Exception $ex) {
                Log::error("Profit: error actualizando orden:" . $orderId);
                Log::error($ex->getMessage());
                $success[] = ["order" => $orderId, "processed" => false];
                continue; //continue
            }
        }

        ///save sync log
        $sync->co_date_end = Carbon::now();
        $sync->save();

        DB::commit();

        return response()->json(['status' => 'ok', 'orders' => $success]);


    }

}

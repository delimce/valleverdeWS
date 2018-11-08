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

    public function getOrdersPaid()
    {

        //ordenes en estatus pago procesado
        $orders = Order::whereOrderStatusId(2)->with(['product', 'customer', 'history', 'totals'])->get();

        $orders->each(function ($item) {

            try {
                $orderDetail = '';
                $orderDetail .= $item->order_id . ';';
                $orderDetail .= $item->date() . ';';
                $orderDetail .= $item->customer->rif . ';';
                $orderDetail .= $item->customer->firstname . ' ' . $item->customer->lastname . ';';
                $orderDetail .= $item->paymentAddress() . ';';
                $orderDetail .= $item->customer->telephone . ';';
                ///totals
                $totals = $item->totals()->orderBy('sort_order')->get();
                $totals->each(function ($total) use (&$orderDetail) {
                    if ($total->code == 'sub_total') $orderDetail .= number_format($total->value, 2,'.', '') . ';';
                    if ($total->code == 'tax') $orderDetail .= number_format($total->value, 2,'.', '') . ';';
                    if ($total->code == 'total') $orderDetail .= number_format($total->value, 2,'.', '') . ';';
                });
                $payment = $item->paymentType();
                ///product items
                $prods = $item->product()->get();
                $prods->each(function ($prod) use ($orderDetail,$payment) {
                    print $orderDetail; //order header
                    print $prod->sku . ';' . $prod->quantity . ';' . number_format($prod->price, 2,'.', '') . ';' . number_format($prod->quantity * $prod->price, 2,'.', '') . ';'; //products detail
                    print $payment;
                    print "<br>";
                });

            } catch (\Exception $ex) {
                Log::error($ex->getMessage());
                return true; //continue
            }

        });


    }

}
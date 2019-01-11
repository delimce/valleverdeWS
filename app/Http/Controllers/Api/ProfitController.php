<?php
/**
 * Created by PhpStorm.
 * User: ldelima
 * Date: 2/10/18
 * Time: 1:57 PM
 */

namespace App\Http\Controllers\Api;

use App\Models\Order\Order;
use App\Models\Order\OrderHistory;
use App\Models\Product\Product;
use App\Models\Product\Stock;
use App\Models\Setting;
use Laravel\Lumen\Routing\Controller as BaseController;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Validator;
use Cache;
use DB;
use Carbon\Carbon;


class ProfitController extends BaseController
{
    static $SHIPPING_CODE;
    static $SHIPPING_TAX;


    /**
     * ProfitController constructor.
     */
    public function __construct()
    {
        self::$SHIPPING_CODE = 'FLETE000000'; ///cod de flete en profit
        self::$SHIPPING_TAX = Setting::getIvaTaxValue();
    }


    /**
     * baja las ordenes ya pagadas pendientes pro procesar
     */
    public function getOrdersPaid()
    {

        //ordenes en estatus pago procesado
        $orders = Order::whereOrderStatusId(2)->with(['product', 'customer', 'history', 'totals'])->get();

        $orders->each(
            function ($item) {

                try {
                    $orderDetail = '';
                    $orderDetail .= $item->order_id . ';';
                    $orderDetail .= $item->date() . ';';
                    $orderDetail .= $item->customer->rif . ';';
                    $orderDetail .= $item->customer->firstname . ' ' . $item->customer->lastname . ';';
                    $orderDetail .= $item->paymentAddress() . ';';
                    $orderDetail .= $item->customer->telephone . ';';
                    $shipping    = null;
                    ///totals
                    $totals        = $item->totals()->orderBy('sort_order')->get();
                    $item_shipping = $item->totals()->whereCode('shipping')->first();
                    if ($item_shipping) {
                        $shipping = [
                            "quantity" => 1,
                            "price"    => number_format(
                                $item_shipping->value,
                                2, '.', ''
                            ),
                            "cod"      => self::$SHIPPING_CODE
                        ];
                    }

                    $totals->each(
                        function ($total) use (&$orderDetail, &$shipping) {
                            ///detalle y impuesto y total
                            if ($total->code == 'sub_total') {
                                $orderDetail .= number_format(
                                        $total->value + $this->getShippingTax($shipping, 'cost'), 2, '.', ''
                                    ) . ';';
                            }
                            if ($total->code == 'tax') {
                                $orderDetail .= number_format(
                                        $total->value + $this->getShippingTax($shipping, 'tax'), 2, '.', ''
                                    ) . ';';
                            }
                            if ($total->code == 'total') {
                                $orderDetail .= number_format($total->value, 2, '.', '') . ';';
                            }
                        }
                    );
                    $payment = $item->paymentType();
                    ///product items
                    $prods = $item->product()->get();
                    $prods->each(
                        function ($prod) use ($orderDetail, $payment) {
                            ///buscando cod de producto profit
                            $profit = $prod->stock()->first()->cod;
                            if ($profit) { ///el codigo existe en profit
                                print $orderDetail; //order header
                                print $profit . ';' . $prod->quantity . ';' . number_format(
                                        $prod->price, 2, '.', ''
                                    ) . ';' . number_format($prod->total, 2, '.', '') . ';'; //products detail
                                print $payment;
                                print "\n";
                            }
                        }
                    );

                    ///revisando si existe item de envio
                    if ($shipping) {
                        print $orderDetail; //order header
                        print $shipping['cod'] . ';' . $shipping['quantity'] . ';' . number_format(
                                $shipping['price'], 2, '.', ''
                            ) . ';' . number_format(
                                $shipping['quantity'] * $shipping['price'], 2, '.', ''
                            ) . ';'; //products detail
                        print $payment;
                        print "\n";
                    }

                } catch (\Exception $ex) {
                    Log::error("Profit: error obteniendo orden:" . $item->order_id);
                    Log::error($ex->getMessage());

                    return true; //continue
                }

            }
        );


    }


    /**marca las ordenes procesadas de profit
     *
     * @param $orders
     * @param $docs
     *
     * @return
     */
    public function setOrderProcessed($orders, $docs)
    {
        $orders  = explode(",", $orders);
        $docs    = explode(",", $docs);
        $success = [];
        DB::beginTransaction();
        foreach ($orders as $i => $orderId) {
            try {
                $order = Order::whereOrderStatusId(2)->find($orderId);
                if ($order != null) {
                    $order->order_status_id = 15; //facturada
                    $order->invoice_no      = $docs[$i]; //nro factura
                    $order->save();
                    $history                  = new OrderHistory();
                    $history->order_status_id = 15;
                    $history->notify          = 0;
                    $history->comment         = 'Facturación Profit, nro factura: ' . $docs[$i];
                    $history->date_added      = Carbon::now();
                    $order->history()->save($history);
                    $success[] = ["order" => $order->order_id, "processed" => true];
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
        DB::commit();

        return response()->json(['status' => 'ok', 'orders' => $success]);
    }


    /**
     * @param $orders
     *
     * @return mixed
     */
    public function setOrderUnProcessed($orders)
    {
        $orders  = explode(",", $orders);
        $success = [];
        DB::beginTransaction();
        foreach ($orders as $i => $orderId) {
            try {
                $order = Order::whereOrderStatusId(15)->find($orderId);
                if ($order != null) {
                    $order->order_status_id = 2; //facturada
                    $order->invoice_no      = ''; //nro factura
                    $order->save();
                    $history                  = new OrderHistory();
                    $history->order_status_id = 2;
                    $history->notify          = 0;
                    $history->comment         = 'restaurando factura profit';
                    $history->date_added      = Carbon::now();
                    $order->history()->save($history);
                    $success[] = ["order" => $order->order_id, "unprocessed" => true];
                } else {
                    $success[] = ["order" => $orderId, "unprocessed" => false];
                    continue;
                }
            } catch (\Exception $ex) {
                Log::error("Profit: error devolviendo estatus de orden:" . $orderId);
                Log::error($ex->getMessage());
                $success[] = ["order" => $orderId, "unprocessed" => false];
                continue; //continue
            }
        }
        DB::commit();

        return response()->json(['status' => 'ok', 'orders' => $success]);
    }


    /**calculo del impuesto sin iva e iva del impuesto
     *
     * @param $shiping
     * @param $type
     *
     * @return float|int
     */
    private function getShippingTax($shiping, $type)
    {
        $cost        = ($shiping) ? $shiping['price'] : 0;
        $without_tax = $cost / self::$SHIPPING_TAX;
        $tax         = $cost - $without_tax;
        return ($type == 'tax') ? $tax : $without_tax;

    }


    /**
     * @param Request $req
     *syncing profit stock
     *
     * @return mixed
     */
    public function stockSync(Request $req)
    {
        $data   = $req->json()->all();
        $resume = ["creados" => 0, "actuailzados" => 0, "errores" => 0];
        try {
            if (count($data) > 0) {
                ///syncing stock
                Log::info("Inicio el proceso de sincronizacion de inventario valleverde");
                Log::info("Fecha:" . Carbon::now('America/Caracas'));
                Log::info("Total de productos a procesar:" . count($data));
                array_filter(
                    $data, function ($item) use (&$resume) {
                    try {
                        $myStock = Stock::whereCod($item["co_art"])->first();
                        if (!empty($myStock)) { //editar inventario
                            $resume["actuailzados"]++;
                            $myStock->quantity = $item["stock_act"];
                            $myStock->price    = $item["prec_vta1"]; //ultimo precio
                            $myStock->price2   = $item["prec_mayor"]; //ultimo precio al mayor
                            $myStock->desc     = $item["art_des"];
                            $myStock->update   = Carbon::now('America/Caracas');
                            $myStock->cancel   = (intval($item["stock_act"])) ? 'N' : 'S';
                            $myStock->save();
                        } else {
                            ///creando registro en stock
                            $resume["creados"]++;
                            $newStock           = new Stock();
                            $newStock->cod      = $item["co_art"];
                            $newStock->desc     = $item["art_des"];
                            $newStock->price    = $item["prec_vta1"]; //ultimo precio
                            $newStock->price2   = $item["prec_mayor"]; //ultimo precio al mayor
                            $newStock->sku      = $item["co_lin"] . $item["co_subl"] . $item["co_color"] . $item["co_cat"];
                            $newStock->quantity = $item["stock_act"];
                            $newStock->co_lin   = $item["co_lin"];
                            $newStock->model    = $item["co_subl"];
                            $newStock->color    = $item["co_color"];
                            $newStock->size     = $item["co_cat"];
                            $newStock->update   = Carbon::now('America/Caracas');
                            $newStock->cancel   = (intval($item["stock_act"])) ? 'N' : 'S';
                            $newStock->save();
                            ///creando producto
                        }

                    } catch (\ErrorException $er) {
                        Log::error($er->getMessage());
                        $resume["errores"]++;

                        return true; //continue
                    }
                }
                );

                ///Actualizando productos del inventario
                $products = Stock::getMainProducts(true);
                array_filter(
                    $products, function ($item) {
                    $prod = Product::whereSku($item['sku'])->first();
                    if (!empty($prod)) { ///existe el producto
                        $prod->price         = $item['price'];
                        $prod->price2        = $item['price2'];
                        $prod->quantity      = $item['quantity'];
                        $prod->date_modified = Carbon::now('America/Caracas');
                        ///activando o no el producto si tiene cant > 0 y precio > 0
                        if ($item['price'] > 0 && $item['quantity'] > 0) {
                            $prod->status = 1;
                        } else {
                            $prod->status = 0;
                        }
                        $prod->save();
                    } else {
                        Log::info("NO se encontró el producto con sku:{$item['sku']}");
                        StockController::createProduct($prod);
                    }
                }
                );

                Log::info("fin del proceso de sincronizacion");
                Log::info(
                    "actualizados:{$resume['actuailzados']}, nuevos:{$resume['creados']}, errores:{$resume["errores"]}"
                );

                return response()->json(['status' => 'ok', 'stock' => $resume]);
            } else {
                return response()->json(['status' => 'error', 'message' => "no se ha enviado ningun dato"], 400);
            }
        } catch (\Exception $ex) {
            Log::error($ex->getMessage());

            return response()->json(['status' => 'error', 'message' => $ex->getMessage()], 500);
        }

    }

}
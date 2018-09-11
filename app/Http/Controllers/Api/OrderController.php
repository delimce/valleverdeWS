<?php

namespace App\Http\Controllers\Api;

use Laravel\Lumen\Routing\Controller as BaseController;
use DB;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Validator;


class OrderController extends BaseController
{
    //

    public function addPayment(Request $req)
    {

        $validator = Validator::make($req->all(), [
            'orderId' => 'required',
            'customerId' => 'required',
            'payment_method_id' => 'required',
            'bank_id_destino' => 'required',
            'reference' => 'required',
            'date' => 'required',
            'amount' => 'required',
        ], ['required' => 'El campo :attribute es requerido',
        ]);

        if ($validator->fails()) {
            $error = $validator->errors()->first();
            return response()->json(['status' => 'error', 'message' => $error], 400);
        }

        /////creando el pago

        try {

            DB::beginTransaction();

            $payment = new OrderPayment();
            $payment->order_id = $req->orderId;
            $payment->customer_id = $req->customerId;
            $payment->payment_method_id = $req->payment_method_id;

            if ($req->payment_method_id == 3) {
                $payment->bank_id_origen = $req->bank_id_origen;
            }

            $payment->bank_id_destino = $req->bank_id_destino;
            $payment->reference = $req->reference;
            $payment->date = $req->date;
            $payment->amount = $req->amount;
            if ($req->has("comments"))
                $payment->comments = $req->comments;
            $payment->dateadd = Carbon::now();
            $payment->save();


            ////creando nuevo historial de la orden

            $history = new OrderHistory();
            $history->order_id = $req->orderId;
            $history->order_status_id = 2;
            $history->notify = 1;
            if ($req->has("comments"))
                $history->comment = $req->comments;
            $history->date_added = Carbon::now();
            $history->save();


            /////actualizando el estatus de la orden

            $orden = Order::findOrFail($req->orderId);
            $orden->order_status_id = 2;
            $orden->save();


            //  $this->sendEmailReportado($req->customerId, $req->orderId); ///enviando email para el cliente

            DB::commit();

            return response()->json(['status' => 'ok', 'message' => "Orden reportada con exito"]);


        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);

        }


    }

}

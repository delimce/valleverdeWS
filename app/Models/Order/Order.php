<?php
/**
 * Created by PhpStorm.
 * User: ldelima
 * Date: 10/9/18
 * Time: 12:15 PM
 */

namespace App\Models\Order;

use Illuminate\Database\Eloquent\Model;
use App\Models\Order\CredicardTransfer;
use Carbon\Carbon;

class Order extends Model
{

    protected $table = "oc_order";
    protected $primaryKey = 'order_id';
    public $timestamps = false;

    ///foreing key
    public function customer()
    {
        return $this->belongsTo('App\Models\Customer\Customer', 'customer_id');
    }

    public function history()
    {
        return $this->hasMany('App\Models\Order\OrderHistory', 'order_id');
    }

    public function product()
    {
        return $this->hasMany('App\Models\Order\OrderProduct', 'order_id');
    }

    public function totals()
    {
        return $this->hasMany('App\Models\Order\OrderTotal', 'order_id');
    }

    public function date()
    {
        return Carbon::parse($this->date_added)->format('Y-m-d');
    }

    public function paymentAddress()
    {
        return $this->payment_address_1 . ', ' . $this->payment_city . ', ' . $this->payment_country;
    }

    public function paymentType()
    {
        $paymentData = '';
        switch ($this->payment_code) {
            case 'instapago':
                $payment = CredicardTransfer::whereOrderId($this->order_id)->orderby('id', 'DESC')->first();
                $paymentData .= 'TARJ' . ';' . $payment->reference . ';' . $this->date() . ';' . '001' . ';' . number_format($payment->total, 2, '.', '') . ';';
                break;

            case 'bank_transfer':
                $payment = OrderPayment::whereOrderId($this->order_id)->orderby('payment_id', 'DESC')->first();
                $paymentData .= ($payment->payment_method == 2) ? 'DEPO' : 'TRAN' . ';' . $payment->reference . ';' . $payment->date . ';' . '001' . ';' . number_format($payment->amount, 2, '.', '') . ';';
                break;
            default:
                $paymentData .= ';;;;;';
                break;
        }
        return $paymentData;

    }

}
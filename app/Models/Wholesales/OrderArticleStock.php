<?php
/**
 * Created by PhpStorm.
 * User: delimce
 * Date: 7/17/2019
 * Time: 7:36 AM
 */

namespace App\Models\Wholesales;
use Illuminate\Database\Eloquent\Model;

class OrderArticleStock extends Model
{
    protected $connection = 'wholesale';
    protected $table = "carrito_articulo";
    protected $primaryKey = ['f_cart_id','renglon'];
    public $timestamps = false;


}
<?php
/**
 * Created by PhpStorm.
 * User: ldeLima
 * Date: 14/12/2015
 * Time: 16:14
 */

namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use DB;

class Setting extends Model
{

    protected $table      = "oc_setting";
    protected $primaryKey = 'setting_id';
    public    $timestamps = false;

    protected $visible = ['code', 'key', 'value']; ///only fields that return


    public function getContactInfo()
    {
        $info = DB::table('oc_setting')->whereIn(
            'key',
            [
                'config_telephone',
                'config_email',
                'config_address',
                'config_name'
            ]
        )->get();

        $data = [];
        $info->each(
            function ($item, $key) use (&$data) {
                $data[$item->key] = $item->value;
            }
        );

        return $data;


    }


    /**
     * valor del impuesto IVA vigente
     */
    static public function getIvaTaxValue()
    {
        $tax = 1;
        $query = "SELECT
                    class.title,
                    rate.`name`,
                    round((rate.rate/100)+1,2) as value
                    FROM
                    oc_tax_class AS class
                    INNER JOIN oc_tax_rule AS rule ON class.tax_class_id = rule.tax_class_id
                    INNER JOIN oc_tax_rate AS rate ON rule.tax_rate_id = rate.tax_rate_id";
        $results = DB::select(DB::raw($query));
        foreach ($results as $res){
            if(strtoupper($res->title)=="IVA"){
                $tax = $res->value;
            }
        }
        return $tax;
    }

}
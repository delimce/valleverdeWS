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

    protected $table = "oc_setting";
    protected $primaryKey = 'setting_id';
    public $timestamps = false;

    protected $visible = array('code', 'key', 'value'); ///only fields that return


    public function getContactInfo()
    {
        $info = DB::table('oc_setting')->whereIn('key',
            ['config_telephone',
                'config_email',
                'config_address',
                'config_name'])->get();

        $data = array();
        $info->each(function ($item, $key) use (&$data) {
            $data[$item->key] = $item->value;
        });

        return $data;


    }

}
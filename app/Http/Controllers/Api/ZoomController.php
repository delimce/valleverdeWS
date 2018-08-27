<?php

namespace App\Http\Controllers\Api;

use Laravel\Lumen\Routing\Controller as BaseController;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use Validator;
use Cache;

class ZoomController extends BaseController
{
    //
    //  private $zoom_url = "http://sandbox.grupozoom.com/localhost/htdocs/internet/servicios/webservices";
    private $zoom_url = "http://webservices.grupozoom.com/internet/servicios/webservices/";
    private $client;

    /**
     * ZoomController constructor.
     * @param string $zoom_url
     */
    public function __construct()
    {
        $this->client = new Client([
            // Base URI is used with relative requests
            'base_uri' => $this->zoom_url,
            // You can set any number of default request options.
            'timeout'  => 8.0,
        ]);
    }


    /** ciudades de zoom
     * @return mixed
     */
    public function getCities()
    {

        try {

            $params = array("cod" => "nacional");

            $response = $this->client->request('POST', 'getCiudades',[
                'form_params' => $params
                ]);

            $data = $response->getBody();

            return response()->json(['status' => 'ok', 'data' => json_decode($data, true)]);


        } catch (\Exception $e) {

            Log::error($e); ///log del error
            return response()->json(['status' => 'error', 'message' => "Error en el servicio"], 500);

        }


    }


    public function getRateTypes(){

        try {


            $response = $this->client->request('POST', 'getTipoTarifa');

            $data = $response->getBody();

            return response()->json(['status' => 'ok', 'data' => json_decode($data, true)]);


        } catch (\Exception $e) {

            Log::error($e); ///log del error
            return response()->json(['status' => 'error', 'message' => "Error en el servicio"], 500);

        }

    }








}

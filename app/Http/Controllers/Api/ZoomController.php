<?php

namespace App\Http\Controllers\Api;

use App\Models\Setting;
use App\Models\Order\Order;
use Laravel\Lumen\Routing\Controller as BaseController;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Validator;
use Cache;


class ZoomController extends BaseController
{
    //
    //  private $zoom_url = "http://sandbox.grupozoom.com/localhost/htdocs/internet/servicios/webservices";
    private $zoom_url = "http://webservices.grupozoom.com/internet/servicios/webservices/";
    // private $zoom_ge_url =  "https://ge.grupozoom.com/webservicesge/";
    private $zoom_ge_url = "http://sandbox.grupozoom.com/proveedores/frontend/webservicesge/";
    private $client;
    private $clientGE;


    /******** datos del cliente
     * Código de Cliente: 1
     * Clave: 456789
     * Token: Es generado a través del webservice generarToken(codigo_cliente)
     * Frase Privada: 0uTjWGelDaE3Rh1HX5vF
     */

    private $client_code = 1;
    private $client_pass = '456789';
    private $client_token = '';
    private $client_key = '0uTjWGelDaE3Rh1HX5vF';
    private $client_cert;
    private $client_shipping;


    /**
     * ZoomController constructor.
     * @param string $zoom_url
     */
    public function __construct()
    {

        $settings = Setting::where("code", "shipping_zoom")->get();
        $settings->each(function ($item) {
            if ($item->key == "shipping_zoom_user" && !empty($item->value))
                $this->client_code = $item->value;
            if ($item->key == "shipping_zoom_password" && !empty($item->value))
                $this->client_pass = $item->value;
            if ($item->key == "shipping_zoom_key" && !empty($item->value))
                $this->client_key = $item->value;

        });

        $this->client = new Client([
            // Base URI is used with relative requests
            'base_uri' => $this->zoom_url,
            // You can set any number of default request options.
            'timeout' => 12,
        ]);

        $this->clientGE = new Client([
            // Base URI is used with relative requests
            'base_uri' => $this->zoom_ge_url,
            // You can set any number of default request options.
            'timeout' => 30,
        ]);
    }

    private function getZoomCities()
    {
        $infoCity = Cache::remember('zoomCities', 720, function () { ///12 horas de cache

            $params = array("cod" => "nacional");
            $response = $this->client->request('POST', 'getCiudades', [
                'form_params' => $params
            ]);
            $data = $response->getBody();
            return json_decode($data, true);

        });

        return $infoCity;
    }


    /** ciudades de zoom
     * @return mixed
     */
    public function getCities()
    {

        try {
            $cities = $this->getZoomCities();
            return response()->json(['status' => 'ok', 'data' => $cities]);
        } catch (\Exception $e) {
            Log::error($e); ///log del error
            return response()->json(['status' => 'error', 'message' => "Error en el servicio"], 500);
        }

    }

    public function getDistricts($city_id)
    {
        try {
            $params = array("codciudad" => $city_id);
            $response = $this->client->request('POST', 'getMunicipios', [
                'form_params' => $params
            ]);

            $data = $response->getBody();
            return response()->json(['status' => 'ok', 'data' => json_decode($data, true)]);
        } catch (\Exception $e) {
            Log::error($e); ///log del error
            return response()->json(['status' => 'error', 'message' => "Error en el servicio"], 500);
        }
    }


    public function getParishes(Request $req)
    {
        try {
            $params = $req->json()->all();
            $response = $this->client->request('POST', 'getParroquias', [
                'form_params' => $params
            ]);

            $data = $response->getBody();
            return response()->json(['status' => 'ok', 'data' => json_decode($data, true)]);
        } catch (\Exception $e) {
            Log::error($e); ///log del error
            return response()->json(['status' => 'error', 'message' => "Error en el servicio"], 500);
        }
    }

    public function getOffices(Request $req)
    {
        try {
            $params = $req->json()->all();
            $response = $this->client->request('POST', 'getOficinasGE', [
                'form_params' => $params
            ]);

            $data = $response->getBody();
            return response()->json(['status' => 'ok', 'data' => json_decode($data, true)]);
        } catch (\Exception $e) {
            Log::error($e); ///log del error
            return response()->json(['status' => 'error', 'message' => "Error en el servicio"], 500);
        }
    }


    /**tarifas de grupo zoom
     * @return mixed
     */
    public function getRateTypes()
    {

        try {

            $response = $this->client->request('POST', 'getTipoTarifa');
            $data = $response->getBody();

            return response()->json(['status' => 'ok', 'data' => json_decode($data, true)]);

        } catch (\Exception $e) {

            Log::error($e); ///log del error
            return response()->json(['status' => 'error', 'message' => "Error en el servicio"], 500);

        }

    }


    /**
     * calculo del tipo de tarifa Zoom
     */
    public function getShippingRate(Request $req)
    {

        $validator = Validator::make($req->all(), [
            'quantity' => 'required',
            'city' => 'required',
            'weight' => 'required',
            'cost' => 'required'
        ], ['required' => 'El campo :attribute es requerido',
            'min' => 'El campo :attribute debe ser mayor a :min',
        ]);

        if ($validator->fails()) {
            $error = $validator->errors()->first();
            return response()->json(['status' => 'error', 'message' => $error], 400);
        }


        ////tipo tarifa 2 envio nacional,
        ////modalidad 2 puerta a puerta
        ////pais 0 venezuela


        try {

            ///buscar codigo por ciudad y estado
            $infoCity = $this->getZoomCities();

            ////busqueda de la ciudad CARACAS
            $CODORIGEN = 19;
            $CODDESTINO = $infoCity[array_search(strtoupper($req->city), array_column($infoCity, 'nombre_ciudad'))];

            $params = array("tipo_tarifa" => "2", "modalidad" => "2",
                "ciudad_origen" => $CODORIGEN, "ciudad_destino" => $CODDESTINO["codciudad"], "oficina_destino" => 0, "cant_piezas" => $req->quantity, "peso" => $req->weight, "valor_mercancia" => $req->cost, "valor_declarado" => $req->cost);

            $response = $this->client->request('POST', 'CalcularTarifa', [
                'form_params' => $params
            ]);

            $data = json_decode($response->getBody(), true);

            if (isset($data["errormessage"])) {

                return response()->json(['status' => 'error', 'message' => $data["errormessage"]], 400);

            } else {
                return response()->json(['status' => 'ok', 'message' => $data]);
            }

        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);

        }


    }


    public function getStatus()
    {

        $response = $this->client->request('POST', 'getEstatus');
        $data = json_decode($response->getBody(), true);
        return response()->json(['status' => 'ok', 'data' => $data]);

    }


    public function getClientServices()
    {

        $params = array("codigo_cliente" => $this->client_code, "clave_acceso" => $this->client_pass);
        $response = $this->clientGE->request('POST', 'getServiciosCliente', [
            'form_params' => $params
        ]);

        $data = json_decode($response->getBody(), true);

        return response()->json(['status' => 'ok', 'data' => $data]);

    }


    /**************************************************************************
     *************************************************************************** GUIA ELECTRONICA
     ***************************************************************************
     */

    /**create shipping
     * @param Request $req
     * @return mixed
     */
    public function createGE(Request $req)
    {

        $validator = Validator::make($req->all(), [
            'orderId' => 'required',
        ], ['required' => 'El campo :attribute es requerido',
        ]);

        if ($validator->fails()) {
            $error = $validator->errors()->first();
            return response()->json(['status' => 'error', 'message' => $error], 400);
        }

        try {

            ///get order's info
            $settings = new Setting();
            $data = $settings->getContactInfo(); ///datos de la empresa valleverde
            $order = Order::findOrFail($req->input('orderId'));
            $shipping = $order->totals()->whereCode("shipping")->first();
            $this->client_cert = $this->getZoomCert();
            ///buscar codigo por ciudad y estado
            $infoCity = $this->getZoomCities();
            $city = $infoCity[array_search(strtoupper($order->shipping_city), array_column($infoCity, 'nombre_ciudad'))];

            $params = array(
                "codigo_cliente" => $this->client_code,
                "clave_acceso" => $this->client_pass,
                "certificado" => $this->client_cert,
                "codservicio" => "2", //guia GUIA PREPAGADA CARGA DIEZ KILOS
                "consignacion" => "t", //Enviar el valor 't' para indicar que el servicio a utilizar es a consignación. Solo aplica para la Familia Prepagada.
                "contacto_remitente" => $data['config_name'], //Persona contacto del Remitente del Envío.
                "ciudad_remitente" => "19", //Código de la Ciudad del Remitente. DISTRITO CAPITAL
                "municipio_remitente" => "101", //LIBERTADOR
                "parroquia_remitente" => "10122", //SUCRE, 10122 23 DE ENE
                "zona_postal_remitente" => "1030", //SUCRE, 1030
                "telefono_remitente" => $data['config_telephone'],
                "direccion_remitente" => $data['config_address'],
                "inmueble_remitente" => "Edificio",
                "retirar_oficina" => 0,
                "codigo_ciudad_destino" => $city["codciudad"],
                "municipio_destino" => "",
                "parroquia_destino" => "",
                "zona_postal_destino" => "",
                "codigo_oficina_destino" => "", //ZOOM LA URBINA
                "destinatario" => $order->firstname . ' ' . $order->lastname,
                "contacto_destino" => $order->shipping_firstname . ' ' . $order->shipping_lastname,
                "cirif_destino" => $order->customer->rif,
                "telefono_destino" => $order->telephone,
                "direccion_destino" => $order->shipping_address_1,
                "inmueble_destino" => "Residencia",
                "siglas_casillero" => "",
                "codigo_casillero" => "",
                "descripcion_contenido" => "ZAPATOS PARA NIÑOS",
                "referencia" => "pedido:" . $order->order_id,
                "numero_piezas" => $order->product()->sum('quantity'),
                "peso_bruto" => 2,
                "tipo_envio" => "M", //'M' para MERCANCIA. Este valor es suministrado
                "valor_declarado" => (round($shipping->value) == 0) ? number_format($order->total, 2) : number_format($shipping->value, 2),
                //  "modalidad_cod" => "",
                "valor_mercancia" => 0
            );


          //  dd($params);

            $response = $this->clientGE->request('POST', 'createShipment', [
                'form_params' => $params
            ]);
            $result = json_decode($response->getBody(), true);
            $this->client_shipping = $result['numguia'];
            return $this->createPDF(); //PDF
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);

        }


    }


    /**get shipping method
     * @param $number
     * @return mixed
     */
    public function getShipping($number)
    {
        $this->client_shipping = $number;
        return $this->createPDF(); //PDF
    }

    private function getZoomToken()
    {
        ///getting token
        $params = array("codigo_cliente" => $this->client_code, "clave" => $this->client_pass);
        $response = $this->clientGE->request('POST', 'generarToken', [
            'form_params' => $params
        ]);
        $result = json_decode($response->getBody(), true);
        return $result['token'];
    }

    private function getZoomCert()
    {
        ///getting token
        $this->client_token = $this->getZoomToken();
        ///getting cert
        $params = array("codigo_cliente" => $this->client_code, "token" => $this->client_token);
        $response = $this->clientGE->request('POST', 'updatesTokens', [
            'form_params' => $params
        ]);

        $getresponse = json_decode($response->getBody(), true);

        if ($getresponse == 'tokenupd') {
            ob_end_clean();
            $clave = md5($this->client_pass);
            $cert = crypt($this->client_code . $clave . $this->client_token, "$1$" . $this->client_key);
            return $cert;

        } else {
            ob_end_clean();
            return response()->json(['status' => 'error', 'message' => "Error al Obtener el Certificado"], 500);
        }
    }

    private function createPDF()
    {
        try {
            $params = array("codigo_cliente" => $this->client_code, "clave" => $this->client_pass, "numero_guia" => $this->client_shipping);
            $response = $this->clientGE->request('POST', 'generarPDF', [
                'form_params' => $params
            ]);

            $result = json_decode($response->getBody(), true);
            $data = array("number" => $this->client_shipping, "pdf" => $result['objetopdf']);
            return response()->json(['status' => 'ok', 'data' => $data]);

        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);

        }
    }


}

<?php

namespace App\Http\Controllers\Api;

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
            'timeout' => 10,
        ]);

        $this->clientGE = new Client([
            // Base URI is used with relative requests
            'base_uri' => $this->zoom_ge_url,
            // You can set any number of default request options.
            'timeout' => 10,
        ]);
    }


    /** ciudades de zoom
     * @return mixed
     */
    public function getCities()
    {

        try {

            $params = array("cod" => "nacional");
            $response = $this->client->request('POST', 'getCiudades', [
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

            $infoCity = Cache::remember('zoomCities', 720, function () { ///12 horas de cache

                $params = array("cod" => "nacional");
                $response = $this->client->request('POST', 'getCiudades', [
                    'form_params' => $params
                ]);

                $data = $response->getBody();
                return json_decode($data, true);

            });


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
            // $errorMens = $e->errorInfo[2];
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

        print_r($data);

        // return $data["token"];

    }


    /**************************************************************************
     *************************************************************************** GUIA ELECTRONICA
     ***************************************************************************
     */

    public function createGE(Request $req)
    {

        $validator = Validator::make($req->all(), [
            'orderId' => 'required',
            'customerId' => 'required',
            'quantity' => 'required',
            'weight' => 'required',
            'cost' => 'required',
        ], ['required' => 'El campo :attribute es requerido',
        ]);

        if ($validator->fails()) {
            $error = $validator->errors()->first();
            return response()->json(['status' => 'error', 'message' => $error], 400);
        }


        print  $this->client_cert = $this->getZoomCert();

        $params = array("codigo_cliente" => $this->client_code,
            "clave_acceso" => $this->client_pass,
            "certificado" => $this->client_cert,
            "codservicio" => "71", //guia GUIA PREPAGADA CARGA DIEZ KILOS
            "consignacion" => "t", //Enviar el valor 't' para indicar que el servicio a utilizar es a consignación. Solo aplica para la Familia Prepagada.
            "contacto_remitente" => "", //Persona contacto del Remitente del Envío.
            "ciudad_remitente" => "", //Código de la Ciudad del Remitente.
            "municipio_remitente" => "",
            "parroquia_remitente" => "",
            "zona_postal_remitente" => "",
            "telefono_remitente" => "",
            "direccion_remitente" => "",
            "inmueble_remitente" => "",
            "retirar_oficina" => null,
            "codigo_ciudad_destino" => "",
            "municipio_destino" => "",
            "parroquia_destino" => "",
            "zona_postal_destino" => "",
            "codigo_oficina_destino" => "",
            "destinatario" => "",
            "contacto_destino" => "",
            "cirif_destino" => "",
            "telefono_destino" => "",
            "direccion_destino" => "",
            "inmueble_destino" => "",
            "siglas_casillero" => "",
            "codigo_casillero" => "",
            "descripcion_contenido" => "",
            "referencia" => null,
            "numero_piezas" => $req->input("quantity"),
            "peso_bruto" => $req->input("weight"),
            "tipo_envio" => "M", //'M' para MERCANCIA. Este valor es suministrado
            "valor_declarado" => null,
            "modalidad_cod" => null,
            "valor_mercancia" => $req->input("cost"),
            "seguro" => null,
            "celular" => null
        );

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


}

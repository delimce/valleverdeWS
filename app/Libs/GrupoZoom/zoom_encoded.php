<?php
include ("class.zoom.json.services.php");
$dominio = $_SERVER['HTTP_HOST'];
/*if(strpos($dominio,"174.127.120.9")!==false) {
	//$url = 'http://174.127.120.9/internet/servicios/webservicesge/';
	$url = 'http://174.127.120.9/localhost/htdocs/proveedores_desarrollo_pg9/webservicesge/';
} else {
	//$url = 'http://www.grupozoom.com/servicios/webservicesge/';
	$url = 'http://172.16.0.13/localhost/htdocs/proveedores_desarrollo_pg9/webservicesge/';
}*/
$url = 'http://174.127.120.9/localhost/htdocs/proveedores_desarrollo_pg9/webservicesge/';
$zoomws = new ZoomJsonService($url);
function zoomCert($codcliente, $clave, $token, $private_key) {
    global $zoomws;
    $response = $zoomws->call("updatesTokens", array("codcliente" => "" . $codcliente . "", "token" => "" . $token . ""), "llamadaExitosa", "llamadaError");
    $getresponse = ob_get_contents();
    if ($getresponse == 'tokenupd') {
        ob_end_clean();
        $clave = md5($clave);
        $certificado = crypt($codcliente . $clave . $token, "$1$" . $private_key);
        return $certificado;
    } else {
        ob_end_clean();
        return 'Error al Obtener el Certificado';
    }
}
function llamadaExitosa($result) {
    ob_start();
    print_r($result);
}
function llamadaError($error) {
    ob_start();
    print_r($error->errormessage);
}
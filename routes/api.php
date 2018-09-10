<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/', function () use ($router) {
    return $router->app->version();
});


//initial
$router->group(['namespace' => 'Api'], function () use ($router) {


    //grupo zoom
    $router->group(['prefix' => 'zoom'], function () use ($router) {
        $router->get('/cities', 'ZoomController@getCities');
        $router->get('/rateTypes', 'ZoomController@getRateTypes');
        $router->post('/shippingRate', 'ZoomController@getShippingRate');
        $router->get('/status', 'ZoomController@getStatus');
        $router->get('/services', 'ZoomController@getClientServices');
        $router->get('/createShipping', 'ZoomController@createGE');
    });

    ///Ecommerce
    $router->group(['prefix' => 'eco'], function () use ($router) {
        $router->put('/payment', 'EcommerceController@setPayment');

    });

    ///syncs
    $router->group(['prefix' => 'sync'], function () use ($router) {


    });

});
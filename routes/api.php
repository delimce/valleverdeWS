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
        $router->get('/districts/{city_id}', 'ZoomController@getDistricts');
        $router->get('/parishes', 'ZoomController@getParishes');
        $router->get('/offices', 'ZoomController@getOffices');
        $router->get('/rateTypes', 'ZoomController@getRateTypes');
        $router->post('/shippingRate', 'ZoomController@getShippingRate');
        $router->get('/status', 'ZoomController@getStatus');
        $router->get('/services', 'ZoomController@getClientServices');
        $router->get('/createShipping', 'ZoomController@createGE');
        $router->get('/shipping/{number}', 'ZoomController@getShipping');
    });

    ///Orders
    $router->group(['prefix' => 'order'], function () use ($router) {
        $router->post('/payment', 'OrderController@addPayment');
        $router->get('/{order_id}', 'OrderController@getOrderById');

    });

    ///syncs
    $router->group(['prefix' => 'sync'], function () use ($router) {


    });

});
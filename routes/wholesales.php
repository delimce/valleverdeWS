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


$router->group(['prefix' => 'wholesales', 'namespace' => 'Api'], function () use ($router) {
    $router->get('/testing', 'WholesalesController@index');

    //profit services
    $router->group(['prefix' => 'profit'], function () use ($router) {
        $router->get('/orders', 'WholesalesController@getWholesalesOrders');
        $router->get('/setProcessed/orders/{orders}', 'WholesalesController@setOrderProcessed');
        $router->post('/orders/stock', 'WholesalesController@syncStockOrders');
    });

    
    });

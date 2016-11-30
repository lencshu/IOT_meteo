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

$app->group(['prefix' => 'api'], function($app)
{
    $app->post('meteo','meteoscontroller@createmeteo');
    $app->put('meteo/{id}','meteoscontroller@updatemeteo');
    $app->delete('meteo/{id}','meteoscontroller@deletemeteo');
    $app->get('meteo','meteoscontroller@index');
});

<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::post('register','api\auth\RegisterController@register');

Route::post('login','api\auth\LoginController@login');
Route::post('logout','api\auth\LoginController@logout');


Route::group(['middleware' => 'auth:api'], function(){
    Route::get('me','api\auth\LoginController@me');

    //blog post route start
    Route::get('blog','api\BlogController@index');
    Route::post('blog/store','api\BlogController@store');
    Route::get('blog/edit/{id}','api\BlogController@edit');
    Route::post('blog/update/{id}','api\BlogController@update');
    Route::delete('blog/destroy/{id}','api\BlogController@destroy');
    //blog post route end

    //blog like route start
    Route::post('like','api\LikeController@index');
    Route::post('like/store','api\LikeController@store');
    //blog like route end
});

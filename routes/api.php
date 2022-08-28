<?php

use App\Http\Controllers\CategoriesController;
use App\Http\Controllers\ClientBasketController;
use App\Http\Controllers\DishesController;
use App\Http\Controllers\FastFoodCompaniesController;
use App\Http\Controllers\FavoritesController;
use App\Http\Controllers\GeoController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\OrdersController;
use App\Http\Controllers\PromotionsController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\TariffsController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApiPassportAuthController;

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::match(['options', 'post'], '/login', function () {

})->middleware('cors');
Route::middleware('cors')->group(function () {
    Route::post('login', [ApiPassportAuthController::class, 'login']);
    Route::post('register', ['role' => 'seller', 'uses' => 'App\Http\Controllers\ApiPassportAuthController@register']);
    Route::post('register-client', ['role' => 'client', 'uses' => 'App\Http\Controllers\ApiPassportAuthController@register']);
    Route::post('/forgot-password', [ApiPassportAuthController::class, 'forgotPassword']);
    Route::post('/confirm-code-forgot-password', [ApiPassportAuthController::class, 'confirmCodeForgotPassword']);
    Route::post('/change-forgot-password', [ApiPassportAuthController::class, 'changeForgotPassword']);
    Route::post('/social-login', [ApiPassportAuthController::class, 'socialLogin']);

    Route::middleware('auth:api')->group(function () {
        Route::post('logout', [ApiPassportAuthController::class, 'logout']);
    });
});

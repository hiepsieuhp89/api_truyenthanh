<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DeviceController;
use App\Http\Controllers\AreaController;
use App\Http\Controllers\ProgramController;
use App\Http\Controllers\API\ProgramController as ProgramAPIController;
use App\Http\Controllers\API\DeviceController as DeviceAPIController;

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


Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('devices/area/{name}', [DeviceController::class, 'showByArea']);
Route::get('devices/area/{name}/schedule/{date}', [DeviceController::class, 'showByDate']);

Route::get('devices/area/', [DeviceController::class, 'showByArea']);

Route::post('devices/area/linh-dam/play', [DeviceController::class, 'playNow']);

Route::get('play/{deviceCode}/{stringencode}', [ProgramController::class, 'play']);

Route::get('area', [AreaController::class, 'index']);

Route::get('area/{id}/devices', [AreaController::class, 'getDevices']);

//API Resource
Route::apiResource('resource/devices', DeviceAPIController::class);
Route::apiResource('resource/programs', ProgramAPIController::class);







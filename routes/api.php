<?php

use Illuminate\Http\Request;

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

/*Setup Methods.*/ 
Route::get("/boot",
	"ApiController@boot"
)->name("api.boot");

Route::get(
	"/test",
	"ApiController@test_api"
)->name("api.test");
/**/ 
Route::post(
	"/module_reminder_assigner",
	"ApiController@module_reminder_assigner"
)->name("api.module_reminder_assigner");

<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApiController;


Route::get("/hi", function () {
    return response()->json(['data' => "hi!"]);
});

Route::post("/register", [ApiController::class, 'register']);

Route::post("/login", [ApiController::class, 'login']);

Route::group(['middleware' => ['auth:sanctum']], function () {
    Route::get("/user", [ApiController::class, 'getUser']);
    Route::post("/logout", [ApiController::class, 'logout']);
    Route::post("/check-admin", [ApiController::class, 'checkAdmin']);
});

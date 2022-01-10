<?php

use Illuminate\Http\Request;

Route::middleware('auth:api')->get('/auth', function (Request $request) {
    return $request->user();
});

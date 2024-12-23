<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\User\AuthController as AuthUserController;
use App\Http\Controllers\User\BookingController;
use Illuminate\Support\Facades\Session;

Route::get('/', function () {
    Session::put('aa', '11111');
    var_dump(Session::get('aa'));
    return view('welcome');
});

Route::get('/123', function () {
    //Session::put('aa', '11111');
    var_dump(Session::get('aa'));
    die;
    
});



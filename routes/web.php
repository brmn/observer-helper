<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::post(
    '/botman-nwco974ytb23t',
    static function () {
        Log::info('webhook /botman-nwco974ytb23t', [Request::all()]);

        app()->make('botman')->listen();
    }
);

<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::post(
    '/botman-nwco974ytb23t',
    static function () {
        Log::info('webhook /botman-nwco974ytb23t', [Request::all()]);

        $bot = app()->make('botman');

        $bot->hears('hello', static function (\BotMan\BotMan\BotMan $bot) {
            $bot->reply('world');
        });

        $bot->hears(\App\Bot\Purchases\Search::getCommandPattern(), '\App\Bot\Purchases\Search@handle');

        $bot->hears(\App\Bot\Help::getCommandPattern(), '\App\Bot\Help@handle');

        $bot->listen();
    }
);

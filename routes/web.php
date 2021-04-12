<?php

use App\Bot\Help;
use App\Bot\Purchases\PurchasesSearch;
use App\Bot\Watchlists\Inn\WatchlistAddInn;
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

        $bot->hears(Help::getCommandPattern(), '\App\Bot\Help@handle');

        $bot->hears(PurchasesSearch::getCommandPattern(), '\App\Bot\Purchases\PurchasesSearch@handle');

        $bot->hears(WatchlistAddInn::getCommandPattern(), '\App\Bot\Watchlists\Inn\WatchlistAddInn@handle');

        $bot->listen();
    }
);

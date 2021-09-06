<?php

use App\Bot\Help;
use App\Bot\Purchases\PurchasesSearch;
use App\Bot\Watchlists\Inn\WatchlistAddInn;
use BotMan\BotMan\BotManFactory;
use BotMan\BotMan\Drivers\DriverManager;
use BotMan\Drivers\Telegram\TelegramDriver;
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

Route::post(
    '/botman-NP24YNV6TW77RRHH',
    static function () {
        Log::info('webhook /botman-NP24YNV6TW77RRHH', [Request::all()]);

        $config = [
            "telegram" => [
                "token" => config('botman.telegrams.VoteAbroadHotDogBot.token'),
            ],
        ];

        DriverManager::loadDriver(TelegramDriver::class);

        $botman = BotManFactory::create($config);

        $botman->hears('hello', static function (\BotMan\BotMan\BotMan $bot) {
            $bot->reply('Hello yourself.');
        });

        $botman->hears('/start', static function (\BotMan\BotMan\BotMan $bot) {
            $bot->reply(
                "Задавайте вопросы в таком формате\n\n"
                . "такой вопрос номер_УИКа статус(псг|наблюдтатель|другое) текст вопроса\n\n"
                . "Например\n\n"
                . "такой вопрос 8158 псг кто там?\n\n"
            );
        });

        $botman->hears('^такой вопрос (.*)', static function (\BotMan\BotMan\BotMan $bot, $query) {
            //@todo validate uik, asker status, text
            //@todo parse to Ticket
            //@todo save ticket

            $asker = "@{$bot->getUser()->getUsername()} ({$bot->getUser()->getFirstName()}"
                . " {$bot->getUser()->getLastName()}, {$bot->getUser()->getId()})";
            $bot->reply("специально обученные люди ищут ответ, терпение");

            $bot->say("$asker: $query", [-546996460], TelegramDriver::class);
        });

        $botman->listen();
    }
);

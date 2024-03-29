<?php

use App\Bot\Help;
use App\Bot\Purchases\PurchasesSearch;
use App\Bot\Voteabroad\Observer\QuestionConversation;
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
            'telegram' => [
                "token" => config('botman.telegrams.VoteAbroadHotDogBot.token'),
            ],
        ];

        DriverManager::loadDriver(TelegramDriver::class);

        $bot = BotManFactory::create($config, new \BotMan\BotMan\Cache\LaravelCache());

        $bot->setContainer(app());

        $bot->hears('hello', static function (\BotMan\BotMan\BotMan $bot) {
            $bot->reply('Hello yourself.');
        });

        $bot->hears('/start', static function (\BotMan\BotMan\BotMan $bot) {
            $bot->reply(QuestionConversation::getDesc());
        });

        $bot->hears(QuestionConversation::getCommandPattern(), static function (\BotMan\BotMan\BotMan $bot) {
            $bot->startConversation(app()->make(QuestionConversation::class));
        });

        $bot->hears('стоп', static function (\BotMan\BotMan\BotMan $bot) {
            $bot->reply('Прервано. Начните заново. Наберите "вызов"');
        })->stopsConversation();

        $bot->listen();
    }
);

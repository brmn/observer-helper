<?php

declare(strict_types=1);

$bot = app()->make('botman');

$bot->hears(
    'hello',
    static function (\BotMan\BotMan\BotMan $bot) {
        $bot->reply('world');
    }
);

<?php

declare(strict_types=1);

$bot = app()->make('botman');

$bot->hears(\App\Bot\Purchases\Search::getCommandPattern(), '\App\Bot\Purchases\Search@handle');

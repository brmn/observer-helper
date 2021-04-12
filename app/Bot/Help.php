<?php

declare(strict_types=1);

namespace App\Bot;

use App\Bot\Purchases\PurchasesSearch;
use App\Bot\Watchlists\Inn\WatchlistAddInn;
use BotMan\BotMan\BotMan;
use BotMan\BotMan\Messages\Outgoing\OutgoingMessage;
use Exception;
use Log;

final class Help extends Command
{
    protected const COMMAND = '/help';
    protected const DESC = '/help';

    protected const VALIDATION_RULES = [];

    private const COMMANDS = [
        Help::class,
        PurchasesSearch::class,
        WatchlistAddInn::class,
    ];

    public function handle(BotMan $bot): void
    {
        try {
            $bot->reply($this->getHelp());
        } catch (Exception $e) {
            Log::error('bot search process', [$e->getMessage()]);

            $bot->reply("something went wrong");
        }
    }

    public static function getCommand(): string
    {
        return self::COMMAND;
    }

    public static function getCommandPattern(): string
    {
        return "^" . self::COMMAND;
    }

    public static function getDesc(): string
    {
        return self::DESC;
    }

    protected function getParamList(): array
    {
        return array_keys(self::VALIDATION_RULES);
    }

    private function getHelp(): OutgoingMessage
    {
        return OutgoingMessage::create(
            implode("\n\n", array_map(static fn(string $command) => $command::getDesc(), self::COMMANDS))
        );
    }
}

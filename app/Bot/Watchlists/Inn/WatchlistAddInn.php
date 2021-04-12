<?php

declare(strict_types=1);

namespace App\Bot\Watchlists\Inn;

use App\Bot\Command;
use App\Shared\Services\UserRepo;
use App\Shared\ValueObjects\Inn;
use App\Usecases\Watchlists\UserAddsInnToWatchlist;
use BotMan\BotMan\BotMan;
use BotMan\BotMan\Messages\Outgoing\OutgoingMessage;
use Exception;
use InvalidArgumentException;
use Log;
use Validator;

final class WatchlistAddInn extends Command
{
    protected const COMMAND = '/watchlist add inn=';
    protected const DESC = <<<'TAG'
/watchlist add inn=10_or_12_digits
TAG;

    protected const VALIDATION_RULES = [
        'inn' => ['required', 'int'],
        self::REST_OF_THE_QUERY => ['string', 'max:100'],
    ];
    private UserAddsInnToWatchlist $watchlist;

    public function __construct(UserAddsInnToWatchlist $watchlist, UserRepo $userRepo)
    {
        parent::__construct($userRepo);

        $this->watchlist = $watchlist;
    }

    public static function getCommand(): string
    {
        return self::COMMAND;
    }

    public static function getCommandPattern(): string
    {
        return "^" . self::COMMAND . '.*';
    }

    public static function getDesc(): string
    {
        return self::DESC;
    }

    protected function getParamList(): array
    {
        return array_keys(self::VALIDATION_RULES);
    }

    public function handle(BotMan $bot): void
    {
        try {
            $params = $this->getParams($bot);

            $validator = Validator::make($params, self::VALIDATION_RULES);

            if ($validator->fails()) {
                throw new InvalidArgumentException($validator->errors()->toJson());
            }

            $inn = Inn::make($params['inn']);
        } catch (InvalidArgumentException $e) {
            $bot->reply("wrong command format: {$e->getMessage()}");
            $bot->reply("Usage: " . self::getDesc());

            return;
        } catch (Exception $e) {
            Log::error('bot search query', [$e->getMessage()]);

            $bot->reply("something went wrong");

            return;
        }

        try {
            $this->watchlist->process($this->getUser($bot), $inn);

            $bot->reply(OutgoingMessage::create("test inn {$inn->getValue()} added"));
        } catch (Exception $e) {
            Log::error('bot search process', [$e->getMessage()]);

            $bot->reply("something went wrong");
        }
    }
}

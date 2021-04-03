<?php

declare(strict_types=1);

namespace App\Bot\Purchases;

use App\Bot\Command;
use App\Shared\Entities\User;
use App\Shared\Services\UserRepo;
use App\Shared\ValueObjects\Inn;
use App\Usecases\Purchases\DTO\PurchasesSearchQuery;
use App\Usecases\Purchases\DTO\PurchasesSearchResult;
use App\Usecases\Purchases\UserSearchesPurchases;
use BotMan\BotMan\BotMan;
use BotMan\BotMan\Messages\Outgoing\OutgoingMessage;
use Exception;
use InvalidArgumentException;
use Webmozart\Assert\Assert;

final class Search extends Command
{
    protected const COMMAND = '/search';
    protected const PARAMS = [
        'inn' => ['required'],
        'perpage' => ['int', 'max:50'],
        'page' => ['int', 'max:10']
    ];

    private UserSearchesPurchases $search;
    private UserRepo $userRepo;

    public function __construct(UserSearchesPurchases $search, UserRepo $userRepo)
    {
        $this->search = $search;

        $this->userRepo = $userRepo;
    }

    public function handle(BotMan $bot): void
    {
        try {
            $query = $this->makeQuery($bot);
        } catch (InvalidArgumentException $e) {
            $bot->reply("wrong command format: {$e->getMessage()}");

            return;
        } catch (Exception $e) {
            \Log::error('bot search query', [$e->getMessage()]);

            $bot->reply("something went wrong");

            return;
        }

        try {
            $bot->reply($this->format($this->search->process($this->getUser($bot), $query)));
        } catch (Exception $e) {
            \Log::error('bot search process', [$e->getMessage()]);

            $bot->reply("something went wrong");
        }
    }

    public static function getCommand(): string
    {
        return self::COMMAND;
    }

    public static function getCommandPattern(): string
    {
        return self::COMMAND . ' .*';
    }

    protected function getParamList(): array
    {
        return self::PARAMS;
    }

    protected function mapParams(array $params): array
    {
        Assert::notNull($params['inn'], 'empty inn');

        return [
            'query' => empty($params[self::REST_OF_THE_QUERY]) ? null : $params[self::REST_OF_THE_QUERY],
            'inn' => Inn::make($params['inn']),
            'perpage' => (int)$params['perpage'],
            'page' => (int)$params['page'],
        ];
    }

    private function getUser(BotMan $bot): User
    {
        return $this->userRepo->getByTelegramId((int)$bot->getUser()->getId());
    }

    private function makeQuery(BotMan $bot): PurchasesSearchQuery
    {
        return new PurchasesSearchQuery($this->getParams($bot));
    }

    private function format(PurchasesSearchResult $searchResult): OutgoingMessage
    {
        $result = "Contracts found {$searchResult->data['contracts']['total']}\n\n";

        foreach ($searchResult->data['contracts']['data'] as $contract) {
            $result .= "signDate: {$contract['signDate']} / publishDate: {$contract['publishDate']}\n"
                . "price: {$contract['price']} {$contract['currency']['code']}\n"
                . "products: " . implode('; ', array_column($contract['products'], 'name')) . "\n"
                . "{$contract['contractUrl']}\n\n\n";
        }

        return OutgoingMessage::create($result);
    }
}

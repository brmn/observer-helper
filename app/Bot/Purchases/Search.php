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
use Arr;
use BotMan\BotMan\BotMan;
use BotMan\BotMan\Messages\Outgoing\OutgoingMessage;
use Exception;
use InvalidArgumentException;
use Log;
use Str;
use Validator;

/**
 * Class Search
 * @package App\Bot\Purchases
 * @SuppressWarnings("CouplingBetweenObjects")
 * @todo extract SearchQuery class
 * @todo extract SearchFormatter class
 */
final class Search extends Command
{
    protected const COMMAND = '/search';
    protected const DESC = '/search inn=INN [perpage=10] [page=1] [products]';

    protected const VALIDATION_RULES = [
        'inn' => ['required'],
        'perpage' => ['int', 'min:1', 'max:50'],
        'page' => ['int', 'min:1', 'max:10'],
        self::REST_OF_THE_QUERY => ['string', 'max:100'],
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
            Log::error('bot search query', [$e->getMessage()]);

            $bot->reply("something went wrong");

            return;
        }

        try {
            $bot->reply($this->format($this->search->process($this->getUser($bot), $query)));
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
        return self::COMMAND . ' .*';
    }

    protected function getParamList(): array
    {
        return array_keys(self::VALIDATION_RULES);
    }

    private function getUser(BotMan $bot): User
    {
        return $this->userRepo->getByTelegramId((int)$bot->getUser()->getId());
    }

    private function makeQuery(BotMan $bot): PurchasesSearchQuery
    {
        $params = $this->getParams($bot);

        $validator = Validator::make($params, self::VALIDATION_RULES);

        if ($validator->fails()) {
            throw new InvalidArgumentException($validator->errors()->toJson());
        }

        $typedParams = [
            'inn' => Inn::make($params['inn']),
            'query' => empty($params[self::REST_OF_THE_QUERY]) ? null : $params[self::REST_OF_THE_QUERY],
        ];

        if (Arr::has($params, 'perpage')) {
            $typedParams['perpage'] = (int)$params['perpage'];
        }

        if (Arr::has($params, 'page')) {
            $typedParams['page'] = (int)$params['page'];
        }

        return new PurchasesSearchQuery($typedParams);
    }

    private function format(PurchasesSearchResult $searchResult): OutgoingMessage
    {
        $result = "Contracts found {$searchResult->data['contracts']['total']}"
            . " for inn {$searchResult->query->inn->getValue()} query {$searchResult->query->query}"
            . " perpage {$searchResult->query->perpage} page {$searchResult->query->page}\n\n";

        foreach ($searchResult->data['contracts']['data'] as $contract) {
            $result .= "signDate: {$contract['signDate']}\n"
                . "publishDate: {$contract['publishDate']}\n"
                . "price: {$contract['price']} {$contract['currency']['code']}\n"
                . "products: " . Str::limit(implode('; ', array_column($contract['products'], 'name')), 100) . "\n"
                . "{$contract['contractUrl']}\n\n";
        }

        $result .= "\nprev page(not implemented) next page(not implemented)";

        return OutgoingMessage::create($result);
    }
}

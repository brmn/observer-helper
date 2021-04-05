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
use Carbon\Carbon;
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
    protected const DESC = <<<'TAG'
/search inn=10_or_12_digits [perpage=10] [page=1] [datefrom=yyyy-mm-dd] [dateto=yyyy-mm-dd] [query]
TAG;


    protected const VALIDATION_RULES = [
        'inn' => ['required'],
        'perpage' => ['int', 'min:1', 'max:50'],
        'page' => ['int', 'min:1', 'max:10'],
        'datefrom' => ['date_format:Y-m-d'],
        'dateto' => ['date_format:Y-m-d'],
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
            $bot->reply("Usage: " . self::getDesc());

            return;
        } catch (Exception $e) {
            Log::error('bot search query', [$e->getMessage()]);

            $bot->reply("something went wrong");

            return;
        }

        try {
            foreach ($this->format($this->search->process($this->getUser($bot), $query)) as $message) {
                $bot->reply($message);
            }
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

        if (Arr::has($params, 'datefrom')) {
            $typedParams['dateFrom'] = Carbon::createFromFormat('Y-m-d', $params['datefrom']);
        }

        if (Arr::has($params, 'dateto')) {
            $typedParams['dateTo'] = Carbon::createFromFormat('Y-m-d', $params['dateto']);
        }

        return new PurchasesSearchQuery($typedParams);
    }

    /**
     * @param PurchasesSearchResult $searchResult
     * @return array<OutgoingMessage>
     */
    private function format(PurchasesSearchResult $searchResult): array
    {
        $messages = [];

        $message = "Contracts found {$searchResult->data['contracts']['total']}"
            . " for inn {$searchResult->query->inn->getValue()} query {$searchResult->query->query}"
            . " perpage {$searchResult->query->perpage} page {$searchResult->query->page}\n\n";

        if (!count($searchResult->data['contracts']['data'])) {
            return [OutgoingMessage::create($message)];
        }

        foreach (array_chunk($searchResult->data['contracts']['data'], 10) as $chunk) {
            foreach ($chunk as $contract) {
                $message .= "signDate: {$contract['signDate']}\n"
                    . "publishDate: {$contract['publishDate']}\n"
                    . "price: {$contract['price']} {$contract['currency']['code']}\n"
                    . "products: " . Str::limit(implode('; ', array_column($contract['products'], 'name')), 100) . "\n"
                    . "{$contract['contractUrl']}\n\n";
            }

            $messages[] = OutgoingMessage::create($message);

            $message = '';
        }

        return $messages;
    }
}

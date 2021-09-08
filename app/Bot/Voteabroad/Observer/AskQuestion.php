<?php

declare(strict_types=1);

namespace App\Bot\Voteabroad\Observer;

use App\Shared\ValueObjects\TelegramUsername;
use App\Usecases\Voteabroad\DTO\ObserverAskQuestionDTO;
use App\Usecases\Voteabroad\ObserverAsksQuestion;
use App\Voteabroad\Entities\Observer;
use App\Voteabroad\ValueObjects\ObserverStatus;
use App\Voteabroad\ValueObjects\UIK;
use BotMan\BotMan\BotMan;
use BotMan\BotMan\Messages\Outgoing\OutgoingMessage;
use Exception;
use InvalidArgumentException;
use Log;
use Validator;

final class AskQuestion
{
    private const COMMAND = 'такой вопрос ';
    private const DESC = <<<'TAG'
Начните описывать проблему отправив боту 
есть проблема


Или задавайте вопросы в таком формате

такой вопрос номер_УИКа статус(псг|наблюдтатель|другое) текст вопроса

Например:

такой вопрос 8158 псг кто там?
TAG;

    private ObserverAsksQuestion $observerAsksQuestion;
    private string $queryRegex;
    /** @var string[][] */
    private array $validationRules;

    public function __construct(ObserverAsksQuestion $observerAsksQuestion)
    {
        $this->observerAsksQuestion = $observerAsksQuestion;

        $this->queryRegex = '/(\d{1,5}) (' . implode('|', ObserverStatus::toLabels()) . ') (.*)/ui';

        $this->validationRules = [
            'uik' => ['required', 'int', 'min:1', 'max:99999'],
            'observer_status' => ['required', 'in:' . implode(',', ObserverStatus::toLabels())],
            'text' => ['string', 'max:1000'],
        ];
    }

    public static function getCommand(): string
    {
        return self::COMMAND;
    }

    public static function getCommandPattern(): string
    {
        return "^" . self::COMMAND . '(.*)';
    }

    public static function getDesc(): string
    {
        return self::DESC;
    }

    public function handle(BotMan $bot, string $query): void
    {
        try {
            $parsedQuery = $this->parseQuery($query);
        } catch (Exception $e) {
            $bot->reply("Неверный формат запроса: {$e->getMessage()}\n\n" . self::getDesc());

            return;
        }

        try {
            $this->observerAsksQuestion->process(
                new ObserverAskQuestionDTO(
                    [
                        'observer' => Observer::make(
                            TelegramUsername::make($bot->getUser()->getUsername()),
                            "{$bot->getUser()->getFirstName()} {$bot->getUser()->getLastName()}",
                            ObserverStatus::from(array_flip(ObserverStatus::toArray())[$parsedQuery['observer_status']])
                        ),
                        'uik' => UIK::make((int)$parsedQuery['uik']),
                        'text' => $parsedQuery['text'],
                    ]
                )
            );

            $bot->reply(OutgoingMessage::create('Специально обученные люди ищут ответ, терпение'));
        } catch (Exception $e) {
            Log::error('bot ObserverAsksQuestion process', [$e->getMessage()]);

            $bot->reply('Что-то пошло не так');
        }
    }

    /**
     * @param string $query
     * @return array{uik: int, observer_status: string, text: string}
     */
    private function parseQuery(string $query): array
    {
        $validator = Validator::make(['query' => $query], ['query' => ['required', 'regex:' . $this->queryRegex]]);

        if ($validator->fails()) {
            throw new InvalidArgumentException($validator->errors()->toJson());
        }

        preg_match($this->queryRegex, $query, $matches);

        $result = [
            'uik' => (int)$matches[1],
            'observer_status' => $matches[2],
            'text' => $matches[3],
        ];

        $validator = Validator::make($result, $this->validationRules);

        if ($validator->fails()) {
            throw new InvalidArgumentException($validator->errors()->toJson());
        }

        return $result;
    }
}

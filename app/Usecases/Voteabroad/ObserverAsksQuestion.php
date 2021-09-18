<?php

declare(strict_types=1);

namespace App\Usecases\Voteabroad;

use App\Usecases\Voteabroad\DTO\ObserverAskQuestionDTO;
use App\Voteabroad\Entities\Question;
use App\Voteabroad\Services\Repos\Questions;
use App\Voteabroad\ValueObjects\QuestionStatus;
use BotMan\BotMan\BotMan;
use BotMan\BotMan\BotManFactory;
use BotMan\BotMan\Drivers\DriverManager;
use BotMan\Drivers\Telegram\TelegramDriver;

final class ObserverAsksQuestion
{
    private array $config;
    private BotMan $bot;
    private Questions $questions;

    public function __construct(Questions $questions)
    {
        $this->config = (array)config('voteabroad.hot_line');

        $botConfig = [
            'telegram' => [
                "token" => config('botman.telegrams.VoteAbroadHotDogBot.token'),
            ],
        ];

        DriverManager::loadDriver(TelegramDriver::class);

        $this->bot = BotManFactory::create($botConfig);

        $this->questions = $questions;
    }

    public function process(ObserverAskQuestionDTO $dto): void
    {
        $question = $this->saveQuestion($dto);

        $this->sendToChats($question);
    }

    private function saveQuestion(ObserverAskQuestionDTO $dto): Question
    {
        //@todo save
        return Question::make($dto->observer, $dto->uik, $dto->text, $dto->needHelp, QuestionStatus::open());
    }

    private function sendToChats(Question $question): void
    {
        //@todo add question id
        $this->bot->say($this->makeMessage($question), $this->getChatIds(), TelegramDriver::class);
    }

    /**
     * @return int[]
     */
    private function getChatIds(): array
    {
        return array_unique(
            [
                $this->config['supporters_chat_id'],
                $this->config['backup_chat_id'],
                $this->config['test_chat_id']
            ]
        );
    }

    private function makeMessage(Question $question): string
    {
        return "УИК {$question->getUik()->getNumber()}({$question->getUik()->getCountry()})"
            . " Нужна консультация оператора: " . ($question->isNeedHelp() ? 'да' : 'нет')
            . " {$question->getObserver()->asString()}: {$question->getText()}";
    }
}

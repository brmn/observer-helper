<?php

declare(strict_types=1);

namespace App\Bot\Voteabroad\Observer;

use App\Shared\ValueObjects\TelegramUsername;
use App\Usecases\Voteabroad\DTO\ObserverAskQuestionDTO;
use App\Usecases\Voteabroad\ObserverAsksQuestion;
use App\Voteabroad\Entities\Observer;
use App\Voteabroad\ValueObjects\ObserverStatus;
use App\Voteabroad\ValueObjects\UIK;
use BotMan\BotMan\Messages\Conversations\Conversation;
use BotMan\BotMan\Messages\Incoming\Answer;
use BotMan\BotMan\Messages\Incoming\IncomingMessage;
use Illuminate\Support\Str;

final class QuestionConversation extends Conversation
{
    private const COMMAND = 'есть проблема';

    protected string $uik;
    protected string $status;
    protected string $text;

    private ObserverAsksQuestion $observerAsksQuestion;

    public function __construct(ObserverAsksQuestion $observerAsksQuestion)
    {
        $this->observerAsksQuestion = $observerAsksQuestion;
    }

    public static function getCommandPattern(): string
    {
        return "^" . self::COMMAND . '$';
    }

    public function run(): void
    {
        $this->askUik();
    }

    public function stopsConversation(IncomingMessage $message): bool
    {
        return Str::lower($message->getText()) === 'стоп';
    }

    public function askUik(): void
    {
        $this->ask('Назовите номер УИКа', function (Answer $answer) {
            $this->uik = $answer->getText();

            $this->askStatus();
        });
    }

    public function askStatus(): void
    {
        $this->ask('Укажите ваш статус(псг, наблюдатель, другое)', function (Answer $answer) {
            $this->status = $answer->getText();

            $this->askQuestion();
        });
    }

    public function askQuestion(): void
    {
        $this->ask('Опишите проблему', function (Answer $answer) {
            $this->status = $answer->getText();

            $this->observerAsksQuestion->process(
                new ObserverAskQuestionDTO(
                    [
                        'observer' => Observer::make(
                            TelegramUsername::make($this->getBot()->getUser()->getUsername()),
                            "{$this->getBot()->getUser()->getFirstName()} {$this->getBot()->getUser()->getLastName()}",
                            ObserverStatus::from(array_flip(ObserverStatus::toArray())[$this->status])
                        ),
                        'uik' => UIK::make((int)$this->uik),
                        'text' => $this->text,
                    ]
                )
            );
        });
    }
}

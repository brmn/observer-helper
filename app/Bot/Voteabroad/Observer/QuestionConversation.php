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
use Validator;

final class QuestionConversation extends Conversation
{
    private const COMMAND = 'вызов';

    protected string $uik;
    protected string $status;
    protected string $text;

    public static function getCommandPattern(): string
    {
        return "^" . self::COMMAND . '$';
    }

    public function stopsConversation(IncomingMessage $message): bool
    {
        return Str::lower($message->getText()) === 'стоп';
    }

    public function run(): void
    {
        $this->askUik();
    }

    public function askUik(): void
    {
        $this->ask('Укажите номер УИК', function (Answer $answer) {
            $this->uik = $answer->getText();

            $validator = Validator::make(
                ['uik' => $this->uik],
                ['uik' => ['required', 'int', 'min:1', 'max:99999', 'in:' . implode(',', UIK::allNumbers())]]
            );

            if ($validator->fails()) {
                $this->say('Ошибка ' . $validator->errors()->toJson());

                $this->askUik();

                return;
            }

            $this->askStatus();
        });
    }

    public function askStatus(): void
    {
        $this->ask('Укажите ваш статус(' . implode('|', ObserverStatus::toLabels()) . ')', function (Answer $answer) {
            $this->status = $answer->getText();

            $validator = Validator::make(
                ['status' => $this->status],
                ['status' => ['required', 'in:' . implode(',', ObserverStatus::toLabels())]]
            );

            if ($validator->fails()) {
                $this->say('Ошибка ' . $validator->errors()->toJson());

                $this->askStatus();

                return;
            }

            $this->askQuestion();
        });
    }

    public function askQuestion(): void
    {
        $this->ask('Коротко опишите проблему', function (Answer $answer) {
            $this->text = $answer->getText();

            $validator = Validator::make(
                ['text' => $this->text],
                ['text' => ['required', 'string', 'min:1', 'max:1000']]
            );

            if ($validator->fails()) {
                $this->say('Ошибка ' . $validator->errors()->toJson());

                $this->askQuestion();

                return;
            }

            app()->make(ObserverAsksQuestion::class)->process(
                new ObserverAskQuestionDTO(
                    [
                        'observer' => Observer::make(
                            TelegramUsername::make($this->getBot()->getUser()->getUsername()),
                            "{$this->getBot()->getUser()->getFirstName()} {$this->getBot()->getUser()->getLastName()}",
                            (int)$this->getBot()->getUser()->getId(),
                            ObserverStatus::from(array_flip(ObserverStatus::toArray())[$this->status])
                        ),
                        'uik' => UIK::make((int)$this->uik),
                        'text' => $this->text,
                    ]
                )
            );

            $this->say('Спасибо. Свяжемся с вами в ближайшее время.');
        });
    }
}

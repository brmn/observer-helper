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
    private const DESC = <<<'TAG'
Привет! Это бот для связи с операторами Горячей линии по наблюдению.
Для связи с Горячей линией наберите слово "вызов" (без кавычек).

Если что-то пошло не так, наберите "стоп".
TAG;

    protected string $uik;
    protected string $status;
    protected string $text;
    private string $needHelp;

    public static function getCommandPattern(): string
    {
        return "^" . self::COMMAND . '$';
    }

    public static function getDesc(): string
    {
        return self::DESC;
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
        $this->ask(
            'Укажите ваш статус(' . Str::lower(implode('|', ObserverStatus::toLabels())) . ')',
            function (Answer $answer) {
                $this->status = Str::lower($answer->getText());

                $validator = Validator::make(
                    ['status' => $this->status],
                    ['status' => ['required', 'in:' . Str::lower(implode(',', ObserverStatus::toLabels()))]]
                );

                if ($validator->fails()) {
                    $this->say('Ошибка ' . $validator->errors()->toJson());

                    $this->askStatus();

                    return;
                }

                $this->askQuestion();
            }
        );
    }

    public function askQuestion(): void
    {
        $this->ask('Коротко опишите проблему', function (Answer $answer) {
            $this->text = $answer->getText();

            $validator = Validator::make(
                ['text' => $this->text],
                ['text' => ['required', 'string', 'min:1', 'max:3000']]
            );

            if ($validator->fails()) {
                $this->say('Ошибка ' . $validator->errors()->toJson());

                $this->askQuestion();

                return;
            }

            $this->askNeedHelp();
        });
    }

    public function askNeedHelp(): void
    {
        $this->ask('Нужна консультация оператора? да/нет', function (Answer $answer) {
            $this->needHelp = Str::lower($answer->getText());

            $validator = Validator::make(
                ['need_help' => $this->needHelp],
                ['need_help' => ['required', 'string', 'in:да,нет']]
            );

            if ($validator->fails()) {
                $this->say('Ошибка ' . $validator->errors()->toJson());

                $this->askNeedHelp();

                return;
            }

            $this->finish();
        });
    }

    private function finish(): void
    {
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
                    'needHelp' => $this->needHelp === 'да'
                ]
            )
        );

        $this->say('Спасибо. Свяжемся с вами в ближайшее время.');
    }
}

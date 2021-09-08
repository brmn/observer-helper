<?php

declare(strict_types=1);

namespace App\Infrastructure\Voteabroad\Repos;

use App\Models\Voteabroad\VoteabroadQuestion;
use App\Shared\ValueObjects\TelegramUsername;
use App\Voteabroad\Entities\Observer;
use App\Voteabroad\Entities\Question;
use App\Voteabroad\Entities\Supporter;
use App\Voteabroad\Services\Repos\Questions;
use App\Voteabroad\ValueObjects\ObserverStatus;
use App\Voteabroad\ValueObjects\QuestionStatus;
use App\Voteabroad\ValueObjects\UIK;

final class QuestionsDb implements Questions
{
    public function save(Question $question): void
    {
        $model = $this->makeModel($question);

        $model->save();

        $question->setId($model->id);
    }

    public function byId(int $id): Question
    {
        return $this->makeEntity(VoteabroadQuestion::find($id));
    }

    private function makeEntity(VoteabroadQuestion $question): Question
    {
        return Question::make(
            Observer::make(
                $question->observer['username'],
                $question->observer['fullname'],
                ObserverStatus::from(array_flip(ObserverStatus::toArray())[$question->observer['status']])
            ),
            UIK::make($question->uik),
            $question->text,
            QuestionStatus::from($question->status),
            $question->supporter ? Supporter::make(TelegramUsername::make($question->supporter['username'])) : null,
            $question->id
        );
    }

    private function makeModel(Question $question): VoteabroadQuestion
    {
        return new VoteabroadQuestion(
            [
                'id' => $question->getId(),
            ]
        );
    }
}

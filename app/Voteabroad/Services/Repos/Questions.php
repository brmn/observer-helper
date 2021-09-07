<?php

declare(strict_types=1);

namespace App\Voteabroad\Services\Repos;

use App\Voteabroad\Entities\Question;

interface Questions
{
    public function save(Question $question): void;
    public function byId(int $id): Question;
}

<?php

namespace App\Usecases\Voteabroad\DTO;

use App\Voteabroad\Entities\Observer;
use App\Voteabroad\ValueObjects\UIK;
use Spatie\DataTransferObject\DataTransferObject;

final class ObserverAskQuestionDTO extends DataTransferObject
{
    public Observer $observer;
    public UIK $uik;
    public string $text;
}

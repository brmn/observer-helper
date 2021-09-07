<?php

declare(strict_types=1);

namespace App\Voteabroad\Entities;

use App\Voteabroad\ValueObjects\QuestionStatus;
use App\Voteabroad\ValueObjects\UIK;
use JetBrains\PhpStorm\Pure;

final class Question
{
    private ?int $id;
    private Observer $observer;
    private UIK $uik;
    private string $text;
    private ?Supporter $supporter;
    private QuestionStatus $status;

    private function __construct(
        Observer $observer,
        UIK $uik,
        string $text,
        QuestionStatus $status,
        Supporter $supporter = null,
        int $id = null
    ) {
        $this->observer = $observer;
        $this->uik = $uik;
        $this->text = $text;
        $this->status = $status;
        $this->supporter = $supporter;
        $this->id = $id;
    }

    public static function make(
        Observer $observer,
        UIK $uik,
        string $text,
        QuestionStatus $status,
        ?Supporter $supporter = null,
        int $id = null,
    ): self {
        return new self($observer, $uik, $text, $status, $supporter, $id);
    }

    public function getObserver(): Observer
    {
        return $this->observer;
    }

    public function getUik(): UIK
    {
        return $this->uik;
    }

    public function getText(): string
    {
        return $this->text;
    }

    public function getSupporter(): ?Supporter
    {
        return $this->supporter;
    }

    public function getStatus(): QuestionStatus
    {
        return $this->status;
    }

    public function assignSupporter(Supporter $supporter): void
    {
        $this->supporter = $supporter;

        if ($this->status->equals(QuestionStatus::open())) {
            $this->status = QuestionStatus::assigned();
        }
    }

    public function close(): void
    {
        $this->status = QuestionStatus::closed();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }
}

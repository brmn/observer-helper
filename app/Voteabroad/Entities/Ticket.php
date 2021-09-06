<?php

declare(strict_types=1);

namespace App\Voteabroad\Entities;

use App\Voteabroad\ValueObjects\TicketStatus;
use App\Voteabroad\ValueObjects\UIK;

final class Ticket
{
    private Observer $observer;
    private UIK $uik;
    private string $text;
    private ?Supporter $supporter;
    private TicketStatus $status;

    private function __construct(Observer $observer, UIK $uik, string $text)
    {
        $this->observer = $observer;
        $this->uik = $uik;
        $this->text = $text;
        $this->status = TicketStatus::open();
    }

    public static function make(Observer $observer, UIK $uik, string $text): self
    {
        return new self($observer, $uik, $text);
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

    public function assignSupporter(Supporter $supporter): void
    {
        $this->supporter = $supporter;
    }

    public function getSupporter(): ?Supporter
    {
        return $this->supporter;
    }

    public function setStatus(TicketStatus $status): void
    {
        $this->status = $status;
    }

    public function getStatus(): TicketStatus
    {
        return $this->status;
    }
}

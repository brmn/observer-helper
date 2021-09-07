<?php

declare(strict_types=1);

namespace App\Voteabroad\Entities;

use App\Shared\ValueObjects\TelegramUsername;
use App\Voteabroad\ValueObjects\ObserverStatus;

final class Observer
{
    private TelegramUsername $username;
    private ObserverStatus $status;

    private function __construct(TelegramUsername $username, ObserverStatus $status)
    {
        $this->username = $username;
        $this->status = $status;
    }

    public static function make(TelegramUsername $username, ObserverStatus $status): self
    {
        return new self($username, $status);
    }

    public function asString(): string
    {
        return "{$this->status->value} @{$this->username->value()}";
    }
}

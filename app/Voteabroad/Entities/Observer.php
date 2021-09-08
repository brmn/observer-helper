<?php

declare(strict_types=1);

namespace App\Voteabroad\Entities;

use App\Shared\ValueObjects\TelegramUsername;
use App\Voteabroad\ValueObjects\ObserverStatus;

final class Observer
{
    private TelegramUsername $username;
    private ObserverStatus $status;
    private string $fullName;

    private function __construct(TelegramUsername $username, string $fullName, ObserverStatus $status)
    {
        $this->username = $username;
        $this->status = $status;
        $this->fullName = $fullName;
    }

    public static function make(TelegramUsername $username, string $fullName, ObserverStatus $status): self
    {
        return new self($username, $fullName, $status);
    }

    public function asString(): string
    {
        return "{$this->status->label} @{$this->username->value()}($this->fullName)";
    }
}

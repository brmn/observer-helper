<?php

declare(strict_types=1);

namespace App\Voteabroad\Entities;

use App\Shared\ValueObjects\TelegramUsername;

final class Observer
{
    private TelegramUsername $username;

    private function __construct(TelegramUsername $username)
    {
        $this->username = $username;
    }

    public static function make(TelegramUsername $username): self
    {
        return new self($username);
    }

    public function asString(): string
    {
        return $this->username->value();
    }
}

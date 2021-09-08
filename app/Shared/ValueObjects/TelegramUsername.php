<?php

declare(strict_types=1);

namespace App\Shared\ValueObjects;

final class TelegramUsername
{
    private ?string $username;

    private function __construct(?string $username)
    {
        $this->username = $username;
    }

    public static function make(?string $username): self
    {
        return new self($username);
    }

    public function value(): ?string
    {
        return $this->username;
    }
}

<?php

declare(strict_types=1);

namespace App\Shared\ValueObjects;

use Webmozart\Assert\Assert;

final class TelegramUsername
{
    private string $username;

    private function __construct(string $username)
    {
        Assert::minLength($username, 2);
        Assert::startsWith($username, '@');

        $this->username = $username;
    }

    public static function make(string $username): self
    {
        return new self($username);
    }

    public function value(): string
    {
        return $this->username;
    }
}

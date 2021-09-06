<?php

declare(strict_types=1);

namespace App\Voteabroad\ValueObjects;

use Webmozart\Assert\Assert;

class UIK
{
    private int $number;

    private function __construct(int $number)
    {
        Assert::greaterThan($number, 0);

        $this->number = $number;
    }

    public static function make(int $number): self
    {
        return new self($number);
    }

    public function getNumber(): int
    {
        return $this->number;
    }
}

<?php

declare(strict_types=1);

namespace App\Shared\ValueObjects;

use Webmozart\Assert\Assert;

final class Inn
{
    private string $value;

    private function __construct(string $value)
    {
        Assert::true(strlen($value) === 10 || strlen($value) === 12, "wrong inn length");

        Assert::regex($value, '/\d/', "wrong format");

        Assert::true($this->isValidControl($value), "wrong control");

        $this->value = $value;
    }

    public static function make(string $value): self
    {
        return new self($value);
    }

    public function getValue(): string
    {
        return $this->value;
    }

    private function isValidControl(string $inn): bool
    {
        $len = strlen($inn);

        if ($len === 10) {
            return (int)$inn[9] === (((
                            2 * (int)$inn[0] + 4 * (int)$inn[1] + 10 * (int)$inn[2] +
                            3 * (int)$inn[3] + 5 * (int)$inn[4] +  9 * (int)$inn[5] +
                            4 * (int)$inn[6] + 6 * (int)$inn[7] +  8 * (int)$inn[8]
                        ) % 11) % 10);
        }

        if ($len === 12) {
            $num10 = (((
                        7 * (int)$inn[0] + 2 * (int)$inn[1] + 4 * (int)$inn[2] +
                        10 * (int)$inn[3] + 3 * (int)$inn[4] + 5 * (int)$inn[5] +
                        9 * (int)$inn[6] + 4 * (int)$inn[7] + 6 * (int)$inn[8] +
                        8 * (int)$inn[9]
                    ) % 11) % 10);

            $num11 = (((
                        3 * (int)$inn[0] +  7 * (int)$inn[1] + 2 * (int)$inn[2] +
                        4 * (int)$inn[3] + 10 * (int)$inn[4] + 3 * (int)$inn[5] +
                        5 * (int)$inn[6] +  9 * (int)$inn[7] + 4 * (int)$inn[8] +
                        6 * (int)$inn[9] +  8 * (int)$inn[10]
                    ) % 11) % 10);

            return (int)$inn[11] === $num11 && (int)$inn[10] === $num10;
        }

        return false;
    }
}

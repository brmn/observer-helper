<?php

declare(strict_types=1);

namespace App\Voteabroad\ValueObjects;

use Spatie\Enum\Enum;

/**
 * @method static self open()
 * @method static self assigned()
 * @method static self closed()
 */
class QuestionStatus extends Enum
{
    protected static function values(): array
    {
        return [
            'open' => 1,
            'assigned' => 2,
            'closed' => 3,
        ];
    }
}

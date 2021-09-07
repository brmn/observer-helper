<?php

declare(strict_types=1);

namespace App\Voteabroad\ValueObjects;

use Spatie\Enum\Enum;

/**
 * @method static self observer()
 * @method static self psg()
 * @method static self other()
 */
class ObserverStatus extends Enum
{
    protected static function values(): array
    {
        return [
            'observer' => 1,
            'psg' => 2,
            'other' => 3,
        ];
    }

    protected static function labels()
    {
        return [
            'observer' => 'наблюдатель',
            'psg' => 'псг',
            'other' => 'другое',
        ];
    }
}

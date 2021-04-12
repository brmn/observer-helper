<?php

declare(strict_types=1);

namespace App\Usecases\Watchlists;

use App\Shared\Entities\User;
use App\Shared\ValueObjects\Inn;

class UserAddsInnToWatchlist
{
    public function process(User $user, Inn $inn): void
    {
        //@todo
    }
}

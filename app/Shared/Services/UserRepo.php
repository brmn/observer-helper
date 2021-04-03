<?php

declare(strict_types=1);

namespace App\Shared\Services;

use App\Shared\Entities\User;

interface UserRepo
{
    public function getByTelegramId(int $id): User;
}

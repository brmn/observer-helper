<?php

declare(strict_types=1);

namespace App\Infrastructure\Repos;

use App\Shared\Entities\User;
use App\Shared\Services\UserRepo;

class UserRepoHardcode implements UserRepo
{
    public function getByTelegramId(int $id): User
    {
        // TODO: Implement getByTelegramId() method.
        return new User();
    }
}

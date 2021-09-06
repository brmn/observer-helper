<?php

declare(strict_types=1);

namespace App\Voteabroad\Services\Repos;

use App\Voteabroad\Entities\Ticket;

interface Tickets
{
    public function save(Ticket $ticket): void;
    public function byId(int $id): Ticket;
}

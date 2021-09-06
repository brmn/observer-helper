<?php

declare(strict_types=1);

namespace App\Infrastructure\Voteabroad\Repos;

use App\Models\Voteabroad\VoteabroadTicket;
use App\Voteabroad\Entities\Observer;
use App\Voteabroad\Entities\Ticket;
use App\Voteabroad\Services\Repos\Tickets;
use App\Voteabroad\ValueObjects\UIK;

final class TicketsDb implements Tickets
{
    public function save(Ticket $ticket): void
    {
        // TODO: Implement save() method.
    }

    public function byId(int $id): Ticket
    {
        return $this->makeEntity(VoteabroadTicket::find($id));
    }

    private function makeEntity(VoteabroadTicket $ticket): Ticket
    {
        //@todo
        return Ticket::make(new Observer(), UIK::make(1), $ticket->getTable());
    }
}

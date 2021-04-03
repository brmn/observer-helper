<?php

declare(strict_types=1);

namespace App\Usecases\Purchases\DTO;

use App\Shared\ValueObjects\Inn;
use Spatie\DataTransferObject\DataTransferObject;

class PurchasesSearchQuery extends DataTransferObject
{
    public ?string $query;
    public Inn $inn;
    public ?int $perpage;
    public ?int $page;
}

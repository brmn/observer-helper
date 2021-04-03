<?php

declare(strict_types=1);

namespace App\Usecases\Purchases\DTO;

use Spatie\DataTransferObject\DataTransferObject;

final class PurchasesSearchResult extends DataTransferObject
{
    public array $data;
    public PurchasesSearchQuery $query;
}

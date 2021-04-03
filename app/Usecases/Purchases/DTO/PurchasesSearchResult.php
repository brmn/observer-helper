<?php

declare(strict_types=1);

namespace App\Usecases\Purchases\DTO;

use GuzzleHttp\Utils;
use Spatie\DataTransferObject\DataTransferObject;

final class PurchasesSearchResult extends DataTransferObject
{
    public array $data;

    public function __toString(): string
    {
        return Utils::jsonEncode($this->toArray());
    }
}

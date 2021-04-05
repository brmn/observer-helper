<?php

declare(strict_types=1);

namespace App\Usecases\Purchases\DTO;

use App\Shared\ValueObjects\Inn;
use Carbon\Carbon;
use Spatie\DataTransferObject\DataTransferObject;

final class PurchasesSearchQuery extends DataTransferObject
{
    public Inn $inn;
    public int $perpage;
    public int $page;
    public Carbon $dateFrom;
    public Carbon $dateTo;
    public string $sortBy = '-signDate';

    public ?string $query = null;

    public function __construct(array $parameters = [])
    {
        $parameters['perpage'] ??= 50;
        $parameters['page'] ??= 1;
        $parameters['dateFrom'] ??= Carbon::createFromFormat('Y-m-d', '2000-01-01');
        $parameters['dateTo'] ??= Carbon::now();

        parent::__construct($parameters);
    }
}

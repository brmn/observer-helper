<?php

declare(strict_types=1);

namespace App\Usecases\Purchases;

use App\Clearspending\ClearspendingApi;
use App\Shared\Entities\User;
use App\Usecases\Purchases\DTO\PurchasesSearchQuery;
use App\Usecases\Purchases\DTO\PurchasesSearchResult;

final class UserSearchesPurchases
{
    private ClearspendingApi $api;

    public function __construct(ClearspendingApi $api)
    {
        $this->api = $api;
    }

    /**
     * @param User $user
     * @param PurchasesSearchQuery $query
     * @return PurchasesSearchResult
     *
     * @SuppressWarnings("unused")
     */
    public function process(User $user, PurchasesSearchQuery $query): PurchasesSearchResult
    {
        return new PurchasesSearchResult(
            [
                'data' => $this->api->contractsSearch(
                    [
                        'productsearch' => $query->query,
                        'customerinn' => $query->inn->getValue(),
                    ]
                )
            ]
        );
    }
}

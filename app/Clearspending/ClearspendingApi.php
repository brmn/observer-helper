<?php

declare(strict_types=1);

namespace App\Clearspending;

use GuzzleHttp\Client;
use GuzzleHttp\Utils;
use InvalidArgumentException;

use function in_array;

final class ClearspendingApi
{
    private const BASE_URL = 'http://openapi.clearspending.ru/restapi/v3/';

    private Client $http;

    public function __construct(Client $http)
    {
        $this->http = $http;
    }

    public function contractsSearch(array $query): array
    {
        //Искусственное ограничение на выдачу: 500.

        //productsearch - полнотекстовый поиск по всем предметам в контракте;
        //productsearchlist - полнотекстовый поиск по набору предметов в контракте;
        //regnum - выборка контракта по его регистрационному номеру (аналог get);
        //customerinn - поиск всех контрактов, у которых заказчик с заданным ИНН;
        //customerkpp - поиск всех контрактов, у которых заказчик с заданным КПП;
        //supplierinn - поиск всех контрактов, у которых поставщик с заданным ИНН;
        //supplierkpp - поиск всех контрактов, у которых поставщик с заданным КПП;
        //okdp_okpd - поиск по коду ОКДП или ОКПД в контракте;
        //budgetlevel - поиск по уровню бюджета;
        //customerregion - поиск по региону заказчика (используются числовые коды регионов);
        //currentstage - состояние контракта;
        //daterange - дата подписания контракта (dd.mm.yyyy-dd.mm.yyyy);
        //pricerange - диапазон цен контракта (minFloat-maxFloat);
        //placing - тип размещения контракта (TODO: добавить пример);
        //fz - номер федерального закона.
        //perpage - количество записей в одном запросе (max - 50).
        //
        //Допускается сортировка выдачи по:
        //price - сортировка по цене с параметрами [1, -1];
        //signDate - сортировка по дате с параметрами [1, -1].

        //Специальные поля:
        //total - найдено записей;
        //page - страница в выдаче;

        $paramList = [
            'productsearch',
            'productsearchlist',
            'regnum',
            'customerinn',
            'customerkpp',
            'supplierinn',
            'supplierkpp',
            'okdp_okpd',
            'budgetlevel',
            'customerregion',
            'currentstage',
            'daterange',
            'pricerange',
            'placing',
            'fz',
            'perpage',
            'page',
            'sort',
        ];

        $response = $this->http->get(
            self::BASE_URL . 'contracts/search/',
            [
                'query' => $this->filterParams($query, $paramList),
            ]
        );

        \Log::info('clearspending api', [$response->getStatusCode()]);

        $result = Utils::jsonDecode($response->getBody()->getContents(), true);

        if ($result === null) {
            throw new InvalidArgumentException('wrong response format');
        }

        /** @var array<array-key, mixed> $result */
        return $result;
    }

    /**
     * @param array<string,string> $query
     * @param array<string> $paramList
     * @return array<string,string>
     */
    private function filterParams(array $query, array $paramList): array
    {
        return array_filter(
            $query,
            static fn (?string $item, string $key): bool => in_array($key, $paramList, true) && $item !== null,
            ARRAY_FILTER_USE_BOTH
        );
    }
}

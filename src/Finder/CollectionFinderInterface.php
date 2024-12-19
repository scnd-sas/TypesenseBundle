<?php

declare(strict_types=1);

namespace ACSEO\TypesenseBundle\Finder;

interface CollectionFinderInterface
{
    public function rawQuery(TypesenseQuery $query): TypesenseResponse;

    public function query(TypesenseQuery $query): TypesenseResponse;

    public function rawPostQuery(TypesenseQuery $query): TypesenseResponse;

    public function postQuery(TypesenseQuery $query): TypesenseResponse;

    public function hydrateResponse(TypesenseResponse $response): TypesenseResponse;

    public function config(): array;
}

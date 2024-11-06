<?php

declare(strict_types=1);

namespace ACSEO\TypesenseBundle\Manager;

interface FieldManagerInterface
{
    public function getDefinition(array $original): array;
}

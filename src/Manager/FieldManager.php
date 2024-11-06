<?php

declare(strict_types=1);

namespace ACSEO\TypesenseBundle\Manager;

class FieldManager implements FieldManagerInterface
{
    public function getDefinition(array $original): array
    {
        return $original;
    }
}

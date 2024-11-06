<?php

declare(strict_types=1);

namespace ACSEO\TypesenseBundle\Manager;

class FieldManagerChain implements FieldManagerInterface
{
    /**
     * @param iterable<FieldManagerInterface> $fieldManagers
     */
    public function __construct(
        private iterable $fieldManagers = [],
    ) {
    }

    public function getDefinition(string $collectionName, array $fieldDefinition): array
    {
        foreach ($this->fieldManagers as $fieldManager) {
            $fieldDefinition = $fieldManager->getDefinition($collectionName, $fieldDefinition);
        }

        return $fieldDefinition;
    }
}

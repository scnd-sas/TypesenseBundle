<?php

declare(strict_types=1);

namespace ACSEO\TypesenseBundle\Finder;

use ACSEO\TypesenseBundle\Client\CollectionClient;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\ResultSetMappingBuilder;

class CollectionFinder implements CollectionFinderInterface
{
    private $collectionConfig;
    private $collectionClient;
    private $em;

    public function __construct(CollectionClient $collectionClient, EntityManagerInterface $em, array $collectionConfig)
    {
        $this->collectionConfig = $collectionConfig;
        $this->collectionClient = $collectionClient;
        $this->em               = $em;
    }

    public function rawQuery(TypesenseQuery $query): TypesenseResponse
    {
        return $this->search($query);
    }

    public function query(TypesenseQuery $query): TypesenseResponse
    {
        $results = $this->search($query);

        return $this->hydrate($results);
    }

    public function rawPostQuery(TypesenseQuery $query): TypesenseResponse
    {
        $query->collection($this->collectionConfig['typesense_name']);

        $response = $this->collectionClient->multiSearch([$query]);

        return new TypesenseResponse($response['results'][0]);
    }

    public function postQuery(TypesenseQuery $query): TypesenseResponse
    {
        $query->collection($this->collectionConfig['typesense_name']);

        $response = $this->collectionClient->multiSearch([$query]);

        return $this->hydrate(new TypesenseResponse($response['results'][0]));
    }

    public function hydrateResponse(TypesenseResponse $response): TypesenseResponse
    {
        return $this->hydrate($response);
    }

    public function config(): array
    {
        return $this->collectionConfig;
    }

    /**
     * Add database entities to Typesense Response
     *
     * @param TypesenseResponse $results
     * @return TypesenseResponse
     */
    private function hydrate(TypesenseResponse $results) : TypesenseResponse
    {
        $ids             = [];
        $primaryKeyInfos = $this->getPrimaryKeyInfo();
        foreach ($results->getResults() as $result) {
            $id = $result['document'][$primaryKeyInfos['documentAttribute']];
            if ('string' === $primaryKeyInfos['entityAttributeType']) {
                $id = sprintf('\'%s\'', $id);
            }
            $ids[] = $id;
        }

        $hydratedResults = [];
        if (count($ids)) {
            $rsm = new ResultSetMappingBuilder($this->em);
            $rsm->addRootEntityFromClassMetadata($this->collectionConfig['entity'], 'e');
            $tableName       = $this->em->getClassMetadata($this->collectionConfig['entity'])->getTableName();
            $query           = $this->em->createNativeQuery('SELECT * FROM '.$tableName.' WHERE '.$primaryKeyInfos['entityAttribute'].' IN ('.implode(', ', $ids).') ORDER BY FIELD(id, '.implode(', ', $ids).')', $rsm);
            $hydratedResults = $query->getResult();
        }
        $results->setHydratedHits($hydratedResults);
        $results->setHydrated(true);

        return $results;
    }

    private function search(TypesenseQuery $query) : TypesenseResponse
    {
        $result = $this->collectionClient->search($this->collectionConfig['typesense_name'], $query);

        return new TypesenseResponse($result);
    }

    private function getPrimaryKeyInfo()
    {
        foreach ($this->collectionConfig['fields'] as $name => $config) {
            if ($config['type'] === 'primary') {
                return [
                    'entityAttribute' => $config['entity_attribute'],
                    'entityAttributeType' => $config['entity_attribute_type'],
                    'documentAttribute' => $config['name'],
                ];
            }
        }

        throw new \Exception(sprintf('Primary key info have not been found for Typesense collection %s', $this->collectionConfig['typesense_name']));
    }
}

<?php

namespace ANOITCOM\EAVBundle\EAV\ORM\Criteria\PropertyFinder;

use Doctrine\DBAL\Connection;

class PropertyFinder
{

    /**
     * @var Connection
     */
    private Connection $connection;


    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }


    public function getPropertyTypeVariantsByAliases(array $propertyAliases): array
    {
        $qb = $this->connection->createQueryBuilder();

        $qb->from('eav_type_property')
           ->select([ 'eav_type_property.id', 'eav_type_property.alias', 'eav_type_property.value_type' ]);

        $expr = $qb->expr()->in('eav_type_property.alias', ':aliases');

        $stmt = $qb->where($expr)->setParameter('aliases', $propertyAliases, Connection::PARAM_STR_ARRAY)->execute();

        $data = $stmt->fetchAll();

        return $this->makeResultFromRows($data, 'alias');
    }


    private function makeResultFromRows(array $rows, string $keyBy): array
    {
        $result = [];

        foreach ($rows as $row) {
            if ( ! isset($result[$row[$keyBy]])) {
                $result[$row[$keyBy]] = [];
            }

            $result[$row[$keyBy]][] = new PropertyInfo($row['id'], $row['alias'], $row['value_type']);
        }

        return $result;
    }


    public function getPropertyTypeVariantsByIds(array $propertyIds): array
    {
        $qb = $this->connection->createQueryBuilder();

        $qb->from('eav_type_property')
           ->select([ 'eav_type_property.id', 'eav_type_property.alias', 'eav_type_property.value_type' ]);

        $expr = $qb->expr()->in('eav_type_property.id', ':ids');

        $stmt = $qb->where($expr)->setParameter('ids', $propertyIds, Connection::PARAM_STR_ARRAY)->execute();

        $data = $stmt->fetchAll();

        return $this->makeResultFromRows($data, 'id');

    }


    public function getPropertyInfoById(string $propertyTypeId): PropertyInfo
    {
        $qb = $this->connection->createQueryBuilder();

        $stmt = $qb->from('eav_type_property')
                   ->select([ 'eav_type_property.id', 'eav_type_property.alias', 'eav_type_property.value_type' ])
                   ->where('eav_type_property.id = :id')
                   ->setParameter('id', $propertyTypeId)
                   ->execute();

        $propertyRows = $stmt->fetchAll();

        if ( ! count($propertyRows)) {
            throw new \InvalidArgumentException('Property type with id ' . $propertyTypeId . ' not found');
        }

        if (count($propertyRows) > 1) {
            throw new \InvalidArgumentException('Found more than one property type with id ' . $propertyTypeId);
        }

        $propertyRow = $propertyRows[0];

        return new PropertyInfo($propertyRow['id'], $propertyRow['alias'], $propertyRow['value_type']);

    }

}
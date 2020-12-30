<?php

namespace ANOITCOM\EAVBundle\EAV\ORM\Criteria\Order\Entity;

use ANOITCOM\EAVBundle\EAV\ORM\Criteria\JoinHandler\JoinTableParams;
use ANOITCOM\EAVBundle\EAV\ORM\Criteria\Order\OrderCriteriaInterface;
use ANOITCOM\EAVBundle\EAV\ORM\Criteria\Order\OrderExpression\OrderExpression;
use ANOITCOM\EAVBundle\EAV\ORM\Criteria\PropertyFinder\PropertyFinder;
use ANOITCOM\EAVBundle\EAV\ORM\EntityManager\Settings\EAVSettings;
use Doctrine\DBAL\Query\QueryBuilder;

class EntityPropertyValueOrderCriteria implements OrderCriteriaInterface
{

    private string $propertyTypeId;

    private string $dir;

    private ?string $nullsPlace;


    public function __construct(string $propertyTypeId, string $dir, ?string $nullsPlace = null)
    {
        $this->propertyTypeId = $propertyTypeId;
        $this->dir            = $dir;
        $this->nullsPlace     = $nullsPlace;
    }


    public function getExpression(QueryBuilder $qb, EAVSettings $eavSettings, int $uniqueIndex): OrderExpression
    {
        $expressionString = $this->getExpressionString($qb, $eavSettings, $uniqueIndex);
        $direction        = $this->getDirection();
        $joinTables       = $this->getJoinTables($eavSettings, $uniqueIndex);

        return new OrderExpression($expressionString, $direction, $this->nullsPlace, $joinTables);
    }


    private function getExpressionString(QueryBuilder $qb, EAVSettings $eavSettings, int $uniqueIndex): string
    {
        $propertyFinder = new PropertyFinder($qb->getConnection());
        $property       = $propertyFinder->getPropertyInfoById($this->propertyTypeId);

        $valuesTableName = $eavSettings->getTableNameForEntityType(EAVSettings::VALUES);
        $column          = $eavSettings->getColumnNameForValueType($property->getValueType());

        $joinedTableAlias = $this->getJoinedTableAlias($valuesTableName, $uniqueIndex);

        if (strtolower($this->dir) === 'asc') {
            $func = 'min';
        } else {
            $func = 'max';
        }

        return $func . '(' . $joinedTableAlias . '.' . $column . ')';

    }


    private function getJoinedTableAlias(string $tableName, int $uniqueIndex): string
    {
        return $tableName . '_' . $uniqueIndex;
    }


    public function getDirection(): string
    {
        return $this->dir;
    }


    private function getJoinTables(EAVSettings $eavSettings, int $uniqueIndex): array
    {
        $entityTableName  = $eavSettings->getTableNameForEntityType(EAVSettings::ENTITY);
        $valuesTableName  = $eavSettings->getTableNameForEntityType(EAVSettings::VALUES);
        $joinedTableAlias = $this->getJoinedTableAlias($valuesTableName, $uniqueIndex);

        return [
            new JoinTableParams($entityTableName, 'left', $valuesTableName, $joinedTableAlias,
                $entityTableName . '.id = ' . $joinedTableAlias . '.entity_id AND ' . $joinedTableAlias . '.type_property_id = \'' . $this->propertyTypeId . '\''),
        ];
    }
}
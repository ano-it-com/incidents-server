<?php

namespace ANOITCOM\EAVBundle\DependencyInjection;

use ANOITCOM\EAVBundle\EAV\ORM\DBAL\Types\BasicJsonMetaType;
use ANOITCOM\EAVBundle\EAV\ORM\DBAL\Types\BoolType;
use ANOITCOM\EAVBundle\EAV\ORM\DBAL\Types\DateTimeType;
use ANOITCOM\EAVBundle\EAV\ORM\DBAL\Types\DateType;
use ANOITCOM\EAVBundle\EAV\ORM\DBAL\Types\DecimalType;
use ANOITCOM\EAVBundle\EAV\ORM\DBAL\Types\IntType;
use ANOITCOM\EAVBundle\EAV\ORM\DBAL\Types\JsonType;
use ANOITCOM\EAVBundle\EAV\ORM\DBAL\Types\TextType;
use ANOITCOM\EAVBundle\EAV\ORM\Entity\EAVEntity;
use ANOITCOM\EAVBundle\EAV\ORM\Entity\EAVEntityPropertyValue;
use ANOITCOM\EAVBundle\EAV\ORM\Entity\EAVEntityRelation;
use ANOITCOM\EAVBundle\EAV\ORM\Entity\EAVEntityRelationType;
use ANOITCOM\EAVBundle\EAV\ORM\Entity\EAVEntityRelationTypeRestriction;
use ANOITCOM\EAVBundle\EAV\ORM\Entity\EAVType;
use ANOITCOM\EAVBundle\EAV\ORM\Entity\EAVTypeProperty;
use ANOITCOM\EAVBundle\EAV\ORM\Entity\EAVTypePropertyRelation;
use ANOITCOM\EAVBundle\EAV\ORM\Entity\EAVTypePropertyRelationType;
use ANOITCOM\EAVBundle\EAV\ORM\Entity\EAVTypePropertyRelationTypeRestriction;
use ANOITCOM\EAVBundle\EAV\ORM\Entity\EAVTypeRelation;
use ANOITCOM\EAVBundle\EAV\ORM\Entity\EAVTypeRelationType;
use ANOITCOM\EAVBundle\EAV\ORM\Entity\EAVTypeRelationTypeRestriction;
use ANOITCOM\EAVBundle\EAV\ORM\EntityManager\Settings\EAVSettings;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{

    public function getConfigTreeBuilder()
    {
        //TODO add entity base class
        $treeBuilder = new TreeBuilder('eav');
        $rootNode    = $treeBuilder->getRootNode();
        $rootNode
            ->children()
                ->arrayNode('base_tables')
                    ->children()
                        ->arrayNode(EAVSettings::ENTITY)
                            ->children()
                                ->scalarNode('table')->defaultValue('eav_entity')->end()
                                ->scalarNode('class')->defaultValue(EAVEntity::class)->end()
                                ->variableNode('columns')
                                    ->defaultValue([
                                        'id'      => TextType::class,
                                        'type_id' => TextType::class,
                                        'meta'    => BasicJsonMetaType::class,
                                    ])
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode(EAVSettings::TYPE)
                            ->children()
                                ->scalarNode('table')->defaultValue('eav_type')->end()
                                ->scalarNode('class')->defaultValue(EAVType::class)->end()
                                ->variableNode('columns')
                                    ->defaultValue([
                                        'id'    => TextType::class,
                                        'alias' => TextType::class,
                                        'title' => TextType::class,
                                        'meta'  => BasicJsonMetaType::class,
                                    ])
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode(EAVSettings::TYPE_PROPERTY)
                            ->children()
                                ->scalarNode('table')->defaultValue('eav_type_property')->end()
                                ->scalarNode('class')->defaultValue(EAVTypeProperty::class)->end()
                                ->variableNode('columns')
                                    ->defaultValue([
                                        'id'         => TextType::class,
                                        'type_id'    => TextType::class,
                                        'value_type' => IntType::class,
                                        'alias'      => TextType::class,
                                        'title'      => TextType::class,
                                        'meta'       => BasicJsonMetaType::class,
                                    ])
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode(EAVSettings::VALUES)
                            ->children()
                                ->scalarNode('table')->defaultValue('eav_values')->end()
                                ->scalarNode('class')->defaultValue(EAVEntityPropertyValue::class)->end()
                                ->variableNode('columns')
                                    ->defaultValue([
                                        'id'               => TextType::class,
                                        'entity_id'        => TextType::class,
                                        'type_property_id' => TextType::class,
                                        'value_text'       => TextType::class,
                                        'value_int'        => IntType::class,
                                        'value_decimal'    => DecimalType::class,
                                        'value_bool'       => BoolType::class,
                                        'value_datetime'   => [ DateType::class, DateTimeType::class ],
                                        'meta'             => BasicJsonMetaType::class,
                                    ])
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode(EAVSettings::ENTITY_RELATION)
                            ->children()
                                ->scalarNode('table')->defaultValue('eav_entity_relation')->end()
                                ->scalarNode('class')->defaultValue(EAVEntityRelation::class)->end()
                                ->variableNode('columns')
                                    ->defaultValue([
                                        'id'      => TextType::class,
                                        'type_id' => TextType::class,
                                        'from_id' => TextType::class,
                                        'to_id'   => TextType::class,
                                        'meta'    => BasicJsonMetaType::class,
                                    ])
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode(EAVSettings::ENTITY_RELATION_TYPE)
                            ->children()
                                ->scalarNode('table')->defaultValue('eav_entity_relation_type')->end()
                                ->scalarNode('class')->defaultValue(EAVEntityRelationType::class)->end()
                                ->variableNode('columns')
                                    ->defaultValue([
                                        'id'    => TextType::class,
                                        'alias' => TextType::class,
                                        'title' => TextType::class,
                                        'meta'  => BasicJsonMetaType::class,
                                    ])
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode(EAVSettings::ENTITY_RELATION_TYPE_RESTRICTION)
                            ->children()
                                ->scalarNode('table')->defaultValue('eav_entity_relation_type_restriction')->end()
                                ->scalarNode('class')->defaultValue(EAVEntityRelationTypeRestriction::class)->end()
                                ->variableNode('columns')
                                    ->defaultValue([
                                        'id'                      => TextType::class,
                                        'entity_relation_type_id' => TextType::class,
                                        'restriction_type_code'   => TextType::class,
                                        'restriction'             => JsonType::class,
                                        'meta'                    => BasicJsonMetaType::class,
                                    ])
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode(EAVSettings::TYPE_RELATION)
                            ->children()
                                ->scalarNode('table')->defaultValue('eav_type_relation')->end()
                                ->scalarNode('class')->defaultValue(EAVTypeRelation::class)->end()
                                ->variableNode('columns')
                                    ->defaultValue([
                                        'id'      => TextType::class,
                                        'type_id' => TextType::class,
                                        'from_id' => TextType::class,
                                        'to_id'   => TextType::class,
                                        'meta'    => BasicJsonMetaType::class,
                                    ])
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode(EAVSettings::TYPE_RELATION_TYPE)
                            ->children()
                                ->scalarNode('table')->defaultValue('eav_type_relation_type')->end()
                                ->scalarNode('class')->defaultValue(EAVTypeRelationType::class)->end()
                                ->variableNode('columns')
                                    ->defaultValue([
                                        'id'    => TextType::class,
                                        'alias' => TextType::class,
                                        'title' => TextType::class,
                                        'meta'  => BasicJsonMetaType::class,
                                    ])
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode(EAVSettings::TYPE_RELATION_TYPE_RESTRICTION)
                            ->children()
                                ->scalarNode('table')->defaultValue('eav_type_relation_type_restriction')->end()
                                ->scalarNode('class')->defaultValue(EAVTypeRelationTypeRestriction::class)->end()
                                ->variableNode('columns')
                                    ->defaultValue([
                                        'id'                    => TextType::class,
                                        'type_relation_type_id' => TextType::class,
                                        'restriction_type_code' => TextType::class,
                                        'restriction'           => JsonType::class,
                                        'meta'                  => BasicJsonMetaType::class,
                                    ])
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode(EAVSettings::TYPE_PROPERTY_RELATION)
                            ->children()
                                ->scalarNode('table')->defaultValue('eav_type_property_relation')->end()
                                ->scalarNode('class')->defaultValue(EAVTypePropertyRelation::class)->end()
                                ->variableNode('columns')
                                    ->defaultValue([
                                        'id'      => TextType::class,
                                        'type_id' => TextType::class,
                                        'from_id' => TextType::class,
                                        'to_id'   => TextType::class,
                                        'meta'    => BasicJsonMetaType::class,
                                    ])
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode(EAVSettings::TYPE_PROPERTY_RELATION_TYPE)
                            ->children()
                                ->scalarNode('table')->defaultValue('eav_type_property_relation_type')->end()
                                ->scalarNode('class')->defaultValue(EAVTypePropertyRelationType::class)->end()
                                ->variableNode('columns')
                                    ->defaultValue([
                                        'id'    => TextType::class,
                                        'alias' => TextType::class,
                                        'title' => TextType::class,
                                        'meta'  => BasicJsonMetaType::class,
                                    ])
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode(EAVSettings::TYPE_PROPERTY_RELATION_TYPE_RESTRICTION)
                            ->children()
                                ->scalarNode('table')->defaultValue('eav_type_property_relation_type_restriction')->end()
                                ->scalarNode('class')->defaultValue(EAVTypePropertyRelationTypeRestriction::class)->end()
                                ->variableNode('columns')
                                    ->defaultValue([
                                        'id'                             => TextType::class,
                                        'type_property_relation_type_id' => TextType::class,
                                        'restriction_type_code'          => TextType::class,
                                        'restriction'                    => JsonType::class,
                                        'meta'                           => BasicJsonMetaType::class,
                                    ])
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
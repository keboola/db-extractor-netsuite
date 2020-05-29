<?php

declare(strict_types=1);

namespace Keboola\DbExtractor\Configuration;

use Keboola\DbExtractorConfig\Configuration\ActionConfigRowDefinition;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;

class GetTablesConfigDefinition extends ActionConfigRowDefinition
{
    protected function getParametersDefinition(): ArrayNodeDefinition
    {
        $parameters = parent::getParametersDefinition();

        // @formatter:off
        $parameters
            ->children()
                ->arrayNode('tableListFilter')
                    ->children()
                        ->booleanNode('listColumns')->defaultTrue()->end()
                        ->arrayNode('tablesToList')
                            ->prototype('array')
                                ->children()
                                    ->scalarNode('tableName')->end()
                                    ->scalarNode('schema')->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end();
        // @formatter:on

        return $parameters;
    }
}

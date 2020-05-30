<?php

declare(strict_types=1);

namespace Keboola\DbExtractor\NetSuite;

use Keboola\DbExtractorConfig\Configuration\NodeDefinition\DbNode as DefaultDbNode;

class NetSuiteDbNode extends DefaultDbNode
{
    protected function init(): void
    {
        // @formatter:off
        $this
            ->children()
                ->scalarNode('accountId')
                    ->isRequired()
                ->end()
                ->scalarNode('roleId')
                    ->isRequired()
                ->end()
                ->scalarNode('user')
                    ->isRequired()
                ->end()
                ->scalarNode('#password')
                    ->isRequired()
                ->end();
        // SSH node is not supported
        // @formatter:on
    }
}

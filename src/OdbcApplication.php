<?php

declare(strict_types=1);

namespace Keboola\DbExtractor;

use Keboola\DbExtractor\Configuration\GetTablesConfigDefinition;
use Keboola\DbExtractor\NetSuite\NetSuiteDbNode;
use Keboola\DbExtractor\Exception\ApplicationException;
use Keboola\DbExtractorConfig\Config;
use Keboola\DbExtractorConfig\Configuration\ActionConfigRowDefinition;
use Keboola\DbExtractorConfig\Configuration\ConfigRowDefinition;
use Psr\Log\LoggerInterface;

class OdbcApplication extends Application
{
    public function __construct(array $config, LoggerInterface $logger, array $state = [], string $dataDir = '/data/')
    {
        $config['parameters']['data_dir'] = $dataDir;
        $config['parameters']['extractor_class'] = 'OdbcExtractor';

        parent::__construct($config, $logger, $state);
    }

    protected function buildConfig(array $config): void
    {
        if ($this->isRowConfiguration($config)) {
            if ($this['action'] === 'run') {
                $this->config = new Config($config, new ConfigRowDefinition(new NetSuiteDbNode()));
            } elseif ($this['action'] === 'getTables') {
                $this->config = new Config($config, new GetTablesConfigDefinition(new NetSuiteDbNode()));
            } else {
                $this->config = new Config($config, new ActionConfigRowDefinition(new NetSuiteDbNode()));
            }
        } else {
            throw new ApplicationException('Old config format is not supported. Please, use row configuration.');
        }
    }
}

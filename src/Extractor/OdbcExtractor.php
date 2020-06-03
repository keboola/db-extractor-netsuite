<?php

declare(strict_types=1);

namespace Keboola\DbExtractor\Extractor;

use Keboola\Datatype\Definition\GenericStorage;
use Keboola\DbExtractor\DbAdapter\DbAdapter;
use Keboola\DbExtractor\DbAdapter\OdbcDbAdapter;
use Keboola\DbExtractor\Exception\UserException;
use Keboola\DbExtractor\NetSuite\NetSuiteDbAdapter;
use Keboola\DbExtractor\NetSuite\NetSuiteDsnFactory;
use Keboola\DbExtractor\NetSuite\OdbcMetadataProvider;
use Keboola\DbExtractor\TableResultFormat\Exception\ColumnNotFoundException;
use Keboola\DbExtractorConfig\Configuration\ValueObject\ExportConfig;
use Keboola\DbExtractorConfig\Configuration\ValueObject\InputTable;
use Psr\Log\LoggerInterface;

class OdbcExtractor extends BaseExtractor
{
    public const INCREMENTAL_TYPES = ['INTEGER', 'NUMERIC', 'FLOAT', 'TIMESTAMP', 'DATE'];

    private array $parameters;

    public function __construct(array $parameters, array $state, LoggerInterface $logger)
    {
        $this->parameters = $parameters;
        parent::__construct($parameters, $state, $logger);
    }

    public function createDbAdapter(array $dbParams): DbAdapter
    {
        $dsnFactory = new NetSuiteDsnFactory();
        $dsn = $dsnFactory->create($dbParams);
        $maskedDsn = preg_replace('~RTK=[a-zA-Z0-9]+~', 'RTK=***', $dsn);
        $this->logger->info("Connecting to DSN '$maskedDsn'");
        return new NetSuiteDbAdapter(
            $this->logger,
            $dsn,
            $dbParams['user'],
            $dbParams['#password'],
        );
    }

    public function getTables(): array
    {
        $loadColumns = $this->parameters['tableListFilter']['listColumns'] ?? true;
        $whiteList = array_map(
            function (array $table) {
                return new InputTable($table['tableName'], $table['schema']);
            },
            $this->parameters['tableListFilter']['tablesToList'] ?? []
        );

        if ($loadColumns === true && empty($whiteList)) {
            throw new UserException(
                'It is not possible to load all tables with columns. Please use "parameters.tableListFilter".'
            );
        }

        $serializer = $this->getGetTablesMetadataSerializer();
        return $serializer->serialize($this->getMetadataProvider()->listTables($whiteList, $loadColumns));
    }

    public function validateIncrementalFetching(ExportConfig $exportConfig): void
    {
        $table = $this->getMetadataProvider()->getTable($exportConfig->getTable());
        try {
            $column = $table->getColumns()->getByName($exportConfig->getIncrementalFetchingColumn());
        } catch (ColumnNotFoundException $e) {
            throw new UserException(sprintf(
                'Incremental fetching column "%s" not found.',
                $exportConfig->getIncrementalFetchingColumn()
            ), 0, $e);
        }

        $datatype = new GenericStorage($column->getType());
        if (!in_array($datatype->getBasetype(), self::INCREMENTAL_TYPES, true)) {
            throw new UserException(sprintf(
                'Unexpected type "%s" of incremental fetching column "%s". Expected types: %s.',
                $column->getType(),
                $column->getName(),
                implode(', ', self::INCREMENTAL_TYPES),
            ));
        }
    }

    public function simpleQuery(ExportConfig $exportConfig): string
    {
        $sql = [];

        if ($exportConfig->hasColumns()) {
            $sql[] = sprintf('SELECT %s', implode(', ', array_map(
                fn(string $c) => $this->dbAdapter->quoteIdentifier($c),
                $exportConfig->getColumns()
            )));
        } else {
            $sql[] = 'SELECT *';
        }

        $sql[] = sprintf(
            'FROM %s.%s',
            $this->dbAdapter->quoteIdentifier($exportConfig->getTable()->getSchema()),
            $this->dbAdapter->quoteIdentifier($exportConfig->getTable()->getName())
        );

        if ($exportConfig->isIncrementalFetching() && isset($this->state['lastFetchedRow'])) {
            $sql[] = sprintf(
                // intentionally ">=" last row should be included, it is handled by storage deduplication process
                'WHERE %s >= %s',
                $this->dbAdapter->quoteIdentifier($exportConfig->getIncrementalFetchingColumn()),
                $this->dbAdapter->quote($this->state['lastFetchedRow'])
            );
        }

        if ($exportConfig->hasIncrementalFetchingLimit()) {
            $sql[] = sprintf(
                'ORDER BY %s LIMIT %d',
                $this->dbAdapter->quoteIdentifier($exportConfig->getIncrementalFetchingColumn()),
                $exportConfig->getIncrementalFetchingLimit()
            );
        }

        return implode(' ', $sql);
    }

    public function getMaxOfIncrementalFetchingColumn(ExportConfig $exportConfig): ?string
    {
        $sql = sprintf(
            'SELECT MAX(%s) as %s FROM %s.%s',
            $this->dbAdapter->quoteIdentifier($exportConfig->getIncrementalFetchingColumn()),
            $this->dbAdapter->quoteIdentifier($exportConfig->getIncrementalFetchingColumn()),
            $this->dbAdapter->quoteIdentifier($exportConfig->getTable()->getSchema()),
            $this->dbAdapter->quoteIdentifier($exportConfig->getTable()->getName())
        );
        $result = $this->dbAdapter->query($sql, $exportConfig->getMaxRetries())->fetchAll();
        return $result ? $result[0][$exportConfig->getIncrementalFetchingColumn()] : null;
    }

    public function getMetadataProvider(): MetadataProvider
    {
        /** @var OdbcDbAdapter $dbAdapter */
        $dbAdapter = $this->dbAdapter;
        return new OdbcMetadataProvider($dbAdapter);
    }
}

<?php

declare(strict_types=1);

namespace Keboola\DbExtractor\NetSuite;

use Generator;
use Keboola\DbExtractor\DbAdapter\OdbcDbAdapter;
use Keboola\DbExtractor\Extractor\MetadataProvider;
use Keboola\DbExtractor\TableResultFormat\Metadata\Builder\MetadataBuilder;
use Keboola\DbExtractor\TableResultFormat\Metadata\Builder\TableBuilder;
use Keboola\DbExtractor\TableResultFormat\Metadata\ValueObject\Table;
use Keboola\DbExtractor\TableResultFormat\Metadata\ValueObject\TableCollection;
use Keboola\DbExtractorConfig\Configuration\ValueObject\InputTable;

class OdbcMetadataProvider implements MetadataProvider
{
    private OdbcDbAdapter $dbAdapter;

    public function __construct(OdbcDbAdapter $dbAdapter)
    {
        $this->dbAdapter = $dbAdapter;
    }

    public function getTable(InputTable $table): Table
    {
        return $this
            ->listTables([$table])
            ->getByNameAndSchema($table->getName(), $table->getSchema());
    }

    public function listTables(array $whitelist = [], bool $loadColumns = true): TableCollection
    {
        /** @var TableBuilder[] $tableBuilders */
        $tableBuilders = [];
        $primaryKeys = [];
        $builder = MetadataBuilder::create();

        // Tables
        $tables = $whitelist ? $this->queryTables($whitelist) : $this->queryAllTables();
        foreach ($tables as $table) {
            if ($table['TABLE_TYPE'] === 'TABLE' || $table['TABLE_TYPE'] === 'VIEW') {
                $schema = $table['TABLE_OWNER'] ?? 'default';
                $name = $table['TABLE_NAME'];
                $id = "$schema.$name";
                $tableBuilders[$id] = $builder
                    ->addTable()
                    ->setSchema($schema)
                    ->setName($name)
                    ->setType($table['TABLE_TYPE']);

                if ($loadColumns) {
                    $primaryKeys[$id] = iterator_to_array($this->queryPrimaryKeys($schema, $name));
                } else {
                    $tableBuilders[$id]->setColumnsNotExpected();
                }
            }
        }

        // Columns
        if ($loadColumns) {
            $columns = $whitelist ? $this->queryColumns($whitelist) : $this->queryAllColumns();
            foreach ($columns as $column) {
                $tabSchema = $column['TABLE_OWNER'] ?? 'default';
                $tabName = $column['TABLE_NAME'];
                $tabId = "$tabSchema.$tabName";
                if (isset($tableBuilders[$tabId])) {
                    $colName = $column['COLUMN_NAME'];
                    $tableBuilders[$tabId]
                        ->addColumn()
                        ->setName($colName)
                        ->setType($column['TYPE_NAME'])
                        ->setNullable((bool) $column['NULLABLE'])
                        ->setPrimaryKey(in_array($colName, $primaryKeys[$tabId], true));
                }
            }
        }

        return $builder->build();
    }

    private function queryAllTables(): Generator
    {
        $stmt = odbc_tables($this->dbAdapter->getConnection());
        while ($table = odbc_fetch_array($stmt)) {
            yield $table;
        }
        odbc_free_result($stmt);
    }

    /**
     * @param array|InputTable[] $tables
     */
    private function queryTables(array $tables): Generator
    {
        $conn = $this->dbAdapter->getConnection();
        foreach ($tables as $table) {
            $schema = $table->getSchema() === 'default' ? '' : $table->getSchema();
            $stmt = odbc_tables($conn, null, $schema, $table->getName());
            while ($table = odbc_fetch_array($stmt)) {
                yield $table;
            }
            odbc_free_result($stmt);
        }
    }

    private function queryPrimaryKeys(string $schema, string $table): Generator
    {
        $schema = $schema === 'default' ? '' : $schema;
        $stmt = odbc_primarykeys($this->dbAdapter->getConnection(), null, $schema, $table);
        while ($pk = odbc_fetch_array($stmt)) {
            yield $pk['COLUMN_NAME'];
        }
        odbc_free_result($stmt);
    }

    private function queryAllColumns(): Generator
    {
        $stmt = odbc_columns($this->dbAdapter->getConnection());
        while ($column = odbc_fetch_array($stmt)) {
            yield $column;
        }
        odbc_free_result($stmt);
    }

    /**
     * @param array|InputTable[] $tables
     */
    private function queryColumns(array $tables): Generator
    {
        $conn = $this->dbAdapter->getConnection();
        foreach ($tables as $table) {
            $schema = $table->getSchema() === 'default' ? '' : $table->getSchema();
            $stmt = odbc_columns($conn, null, $schema, $table->getName());
            while ($column = odbc_fetch_array($stmt)) {
                yield $column;
            }
            odbc_free_result($stmt);
        }
    }
}

<?php

declare(strict_types=1);

namespace Keboola\DbExtractor\NetSuite;

use Keboola\DbExtractor\DbAdapter\OdbcDbAdapter;
use Keboola\DbExtractor\DbAdapter\QueryResult\QueryResult;

class NetSuiteDbAdapter extends OdbcDbAdapter
{
    public function testConnection(): void
    {
        $this->query('SELECT * FROM MyRoles LIMIT 1', 1);
    }

    protected function doQuery(string $query): QueryResult
    {
        $stmt = $this->checkError(
            @odbc_exec($this->connection, $query) // intentionally @
        );
        return new NetSuiteQueryResult($stmt);
    }
}

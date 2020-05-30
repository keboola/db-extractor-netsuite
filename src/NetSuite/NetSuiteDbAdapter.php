<?php

declare(strict_types=1);

namespace Keboola\DbExtractor\NetSuite;

use Keboola\DbExtractor\DbAdapter\OdbcDbAdapter;

class NetSuiteDbAdapter extends OdbcDbAdapter
{
    public function testConnection(): void
    {
        $this->query('SELECT * FROM MyRoles LIMIT 1', 1);
    }
}

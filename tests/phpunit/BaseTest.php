<?php

declare(strict_types=1);

namespace Keboola\DbExtractor\Tests;

use Keboola\Component\Logger;
use Keboola\DbExtractor\OdbcApplication;
use PHPUnit\Framework\TestCase;

abstract class BaseTest extends TestCase
{
    protected function getDbConfig(): array
    {
        return [
            'accountId' => getenv('ODBC_ACCOUNT_ID'),
            'roleId' => getenv('ODBC_ROLE_ID'),
            'user' => getenv('ODBC_USER'),
            '#password' => getenv('ODBC_PASSWORD'),
        ];
    }

    protected function runApp(array $config): array
    {
        $logger = new Logger();
        $app = new OdbcApplication($config, $logger);
        return $app->run();
    }
}

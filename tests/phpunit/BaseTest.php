<?php

declare(strict_types=1);

namespace Keboola\DbExtractor\Tests;

use Keboola\DbExtractor\OdbcApplication;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
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
        $logger = new Logger('test');
        $logger->pushHandler(new StreamHandler('php://stdout'));
        $app = new OdbcApplication($config, $logger);
        return $app->run();
    }
}

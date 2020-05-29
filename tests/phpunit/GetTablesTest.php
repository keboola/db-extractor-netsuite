<?php

declare(strict_types=1);

namespace Keboola\DbExtractor\Tests;

use Keboola\DbExtractor\Exception\UserException;
use PHPUnit\Framework\Assert;

class GetTablesTest extends BaseTest
{
    public function testGetTables(): void
    {
        $config = [
            'action' => 'getTables',
            'parameters' => [
                'db' => [
                    'accountId' => getenv('ODBC_ACCOUNT_ID'),
                    'roleId' => getenv('ODBC_ROLE_ID'),
                    'user' => getenv('ODBC_USER'),
                    '#password' => getenv('ODBC_PASSWORD'),
                ],
            ],
        ];

        $this->expectException(UserException::class);
        $this->expectExceptionMessage(
            'It is not possible to load all tables with columns. Please use "parameters.tableListFilter".'
        );
        $result = $this->runApp($config);
    }

    public function testGetTablesWithoutColumns(): void
    {
        $config = [
            'action' => 'getTables',
            'parameters' => [
                'db' => $this->getDbConfig(),
                'tableListFilter' => [
                    'listColumns' => false,
                ],
            ],
        ];

        $result = $this->runApp($config);
        Assert::assertTrue(count($result['tables']) > 10);
        Assert::assertTrue(empty($result['tables'][0]['columns']));
    }

    public function testGetSpecifiedTablesWithColumns(): void
    {
        $config = [
            'action' => 'getTables',
            'parameters' => [
                'db' => $this->getDbConfig(),
                'tableListFilter' => [
                    'listColumns' => true,
                    'tablesToList' => [
                        [
                            'tableName' => 'Account',
                            'schema' => 'default',
                        ],
                        [
                            'tableName' => 'Currency',
                            'schema' => 'default',
                        ],
                    ],
                ],
            ],
        ];

        $result = $this->runApp($config);
        $tables = $result['tables'];
        usort($tables, function (array $a, array $b) {
            return $a['name'] <=> $b['name'];
        });
        Assert::assertSame(2, count($tables));
        Assert::assertSame('Account', $tables[0]['name']);
        Assert::assertTrue(count($tables[0]['columns']) > 1);
        Assert::assertSame('Currency', $tables[1]['name']);
        Assert::assertTrue(count($tables[1]['columns']) > 1);
    }
}

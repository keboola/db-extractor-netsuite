<?php

declare(strict_types=1);

namespace Keboola\DbExtractor\Tests;

use PHPUnit\Framework\Assert;

class ConnectionTest extends BaseTest
{
    public function testConnection(): void
    {
        $config = [
            'action' => 'testConnection',
            'parameters' => ['db' => $this->getDbConfig()],
        ];

        $result = $this->runApp($config);
        Assert::assertSame('success', $result['status']);
    }
}

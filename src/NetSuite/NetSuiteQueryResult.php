<?php

declare(strict_types=1);

namespace Keboola\DbExtractor\NetSuite;

use Keboola\DbExtractor\DbAdapter\QueryResult\OdbcQueryResult;

class NetSuiteQueryResult extends OdbcQueryResult
{
    private array $binFields = [];

    /**
     * @param resource $stmt
     */
    public function __construct($stmt)
    {
        parent::__construct($stmt);
        odbc_binmode($stmt, ODBC_BINMODE_PASSTHRU);

        // Load binnary fields
        for ($i=1; $i < odbc_num_fields($stmt); $i++) {
            if (odbc_field_type($stmt, $i) === 'bit') {
                $this->binFields[] = odbc_field_name($stmt, $i);
            }
        }
    }

    public function fetch(): ?array
    {
        return $this->mapRow(parent::fetch());
    }

    protected function mapRow(?array $row): ?array
    {
        if ($row === null) {
            return null;
        }

        // There is some bug in ODBC driver
        // ... for boolean value is returned T/F followed with some non-utf8 chars
        foreach ($this->binFields as $fieldName) {
            if (!empty($row[$fieldName])) {
                $row[$fieldName] = $row[$fieldName][0] === 'T' ? 1 : 0;
            }
        }

        if (isset($row['IsBaseCurrency'])) {
            var_dump($row['IsBaseCurrency']);
        }
        return $row;
    }
}

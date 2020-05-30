<?php

declare(strict_types=1);

namespace Keboola\DbExtractor\NetSuite;

class NetSuiteDsnFactory
{
    public function create(array $dbParams): string
    {
        //$options[] = 'WebServiceHost=' . $dbParams['host'];
        //$options[] = 'Verbosity=4'; // 5
        //$options[] = 'Logfile=/tmp/odbc_logfile';
        $options[] = 'Account Id=' . $dbParams['accountId'];
        $options[] = 'Include Custom Field Columns=false';
        $options[] = 'Role Id=' . $dbParams['roleId'];
        $options[] = 'Readonly=true';
        $options[] = 'UseSessions=false';
        $options[] = 'Logout Unknown Sessions=true';
        $options[] = 'RTK=' . getenv('RTK_LICENSE');
        return sprintf('DRIVER={CData ODBC Driver for NetSuite};%s', implode(';', $options));
    }
}

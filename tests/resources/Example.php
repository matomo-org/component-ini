<?php

return [
    'PostgreSQL' => [
        'developer' => 'Michael Stonebraker',
        'database' => [
            'system' => 'postgres',
            'embedded' => '0',
            'license' => 'PostgreSQL License',
        ],
        'website' => [
            'address' => 'postgresql.org',
        ],
    ],
    'MariaDB' => [
        'developer' => 'Michael Widenius',
        'database' => [
            'system' => 'mariadb',
            'embedded' => '0',
            'license' => 'GPLv2',
        ],
        'website' => [
            'address' => 'mariadb.org',
        ],
    ],
    'SQLite' => [
        'developer' => 'Dwayne Richard Hipp',
        'database' => [
            'system' => 'sqlite',
            'embedded' => '1',
            'license' => 'Public domain',
        ],
        'website' => [
            'address' => 'sqlite.org',
        ],
    ],
];
